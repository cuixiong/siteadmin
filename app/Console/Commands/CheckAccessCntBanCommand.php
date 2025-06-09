<?php
/**
 * CheckAccessCntBanCommand.php UTF-8
 * 校验请求次数,封禁
 *
 * @date    : 2025/5/9 10:27 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Admin\Http\Models\Server;
use Modules\Admin\Http\Models\Site;
use Modules\Admin\Http\Models\SiteNginxConf;
use Modules\Admin\Http\Models\System;
use Modules\Site\Http\Controllers\SiteCrontabController;
use Modules\Site\Http\Models\AccessLog;
use Modules\Site\Http\Models\NginxBanList;
use Modules\Site\Http\Models\SystemValue;
use Modules\Site\Http\Models\System as SiteSystem;
use Modules\Admin\Http\Models\SystemValue as AdminSystemValue;
use phpseclib3\Net\SSH2;

class CheckAccessCntBanCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:check_access_cnt_nginx_ban';

    public function handle() {
        try {
            //设置日志
            config(['logging.default' => 'cli']);
            $is_overseas = env('is_overseas', 1);
            $siteNginxConfList = SiteNginxConf::query()
                                              ->where("is_overseas", $is_overseas)
                                              ->get()->toArray();
            foreach ($siteNginxConfList as $siteNginxConfInfo) {
                $site = $siteNginxConfInfo['name'];
                tenancy()->initialize($site);
                $parent_id = SiteSystem::query()->where("alias", 'access_cnt_nginx_ban')
                                       ->value("id");
                $sysValList = SystemValue::query()->where("parent_id", $parent_id)
                                         ->get()->keyBy("key")->toArray();
                $banStr = $this->getBanNginxStr($sysValList);
                echo "{$siteNginxConfInfo['name']}:nginx封禁字符串:{$banStr}".PHP_EOL;
                if (!empty($banStr)) {
                    $this->writeNginxConf($banStr, $siteNginxConfInfo);
                    $this->reloadNginx();
                }
            }
        } catch (\Exception $e) {
            \Log::error(
                '检测nginx负载异常:'.json_encode([$e->getMessage()]).'  文件路径:'.__CLASS__.'  行号:'.__LINE__
            );
            echo $e->getMessage().PHP_EOL;
            exit;
        }
        echo "当前时间:".date("Y-m-d H:i:s")."服务器正常!".PHP_EOL;
    }

    /**
     *
     * @param  $siteNginxConfInfo
     *
     */
    public function getBanNginxStr($sysValList) {
        //获取当前站点, 异常流量
        $ipMutiType = $sysValList['cntIpMutiType']['value'] ?? 3; //默认3段ip
        if ($ipMutiType == 2) {
            $tab = 'ip_muti_second';
        } elseif ($ipMutiType == 3) {
            $tab = 'ip_muti_third';
        } else {
            $tab = 'ip';
        }
        $start_time = time() - $sysValList['day_ban_rules']['value'] * 3600;
        $day_max_cnt = $sysValList['day_ban_rules']['back_value'] ?? 0;
        if(empty($day_max_cnt ) || $day_max_cnt <=0 ){
            $accessDayIpLogList = [];
        }else{
            $accessDayIpLogList = AccessLog::query()->where("created_at", ">", $start_time)
                                           ->groupBy($tab)
                                           ->selectRaw("count(*) as cnt, ".$tab)
                                           ->having('cnt', '>=', $day_max_cnt)
                                           ->pluck('cnt', $tab)->toArray();
        }

        $start_time = time() - $sysValList['hour_ban_rules']['value'] * 3600;
        $hour_max_cnt = $sysValList['hour_ban_rules']['back_value'] ?? 0;
        if(empty($hour_max_cnt )  || $hour_max_cnt <=0 ){
            $accessHourIpLogList = [];
        }else{
            $accessHourIpLogList = AccessLog::query()->where("created_at", ">", $start_time)
                                            ->groupBy($tab)
                                            ->selectRaw("count(*) as cnt, ".$tab)
                                            ->having('cnt', '>=', $hour_max_cnt)
                                            ->pluck('cnt', $tab)->toArray();
        }
        $accessIpLogList = array_merge($accessDayIpLogList, $accessHourIpLogList);
        $banStr = '';
        foreach ($accessIpLogList as $forIp => $forVal) {
            if ($tab == 'ip_muti_second') {
                $ipstr = 'deny '.$forIp.'.0.0/16;';
            } elseif ($tab == 'ip_muti_third') {
                $ipstr = 'deny '.$forIp.'.0/24;';
            } else {
                $ipstr = 'deny '.$forIp.";";
            }
            $banIpStrList[] = PHP_EOL.$ipstr;
        }
        if (!empty($banIpStrList)) {
            $banIpStrList = array_unique($banIpStrList);
            $banStr = implode('', $banIpStrList);
        }
        $banStr .= PHP_EOL;

        return $banStr;
    }

    public function customEscape($input, $characters) {
        // 转义指定的字符
        $escaped = '';
        foreach (str_split($input) as $char) {
            if (in_array($char, $characters)) {
                $escaped .= '\\'.$char; // 添加单个反斜杠
            } else {
                $escaped .= $char;
            }
        }

        return $escaped;
    }

    /**
     *
     * @param $output
     * @param $return_var
     *
     * @return array
     */
    private function reloadNginx() {
        $nginx_reload_path = 'sh /www/wwwroot/nginx_shell/nginx_reload.sh';
        exec($nginx_reload_path, $output, $return_var);
        if ($return_var) {
            \Log::error('重启nginx失败:'.json_encode($output));
        } else {
            \Log::info('重启nginx成功:'.json_encode($output));
        }
    }

    /**
     * 修改nginx配置
     *
     * @param string $banStr
     * @param        $siteNginxConfInfo
     *
     */
    private function writeNginxConf(string $banStr, $siteNginxConfInfo) {
        //因为是无法解禁, 所以需要考虑覆盖 + 唯一 情况
        $temp_content = file_get_contents($siteNginxConfInfo['access_ban_conf_path']);
        $banList = explode(";", $banStr);
        foreach ($banList as $forbanStr) {
            if (empty($forbanStr) || !strpos($forbanStr, 'deny') !== false) {
                continue;
            }
            if (strpos($temp_content, $forbanStr) !== false) {
                //包含跳过
            } else {
                $forRealBanStr = $forbanStr.";";
                $add_data = [
                    'ban_str'      => $forRealBanStr,
                    'ban_type'     => 1,
                    'service_type' => 2,
                    'created_at'   => time(),
                ];
                NginxBanList::query()->insert($add_data);
                $temp_content .= $forRealBanStr;
            }
        }
        $new_file_path = $siteNginxConfInfo['access_ban_conf_path'];
        file_put_contents($new_file_path, $temp_content);
    }

    /**
     * 远程服务器执行命令
     *
     * @param \phpseclib3\Net\SSH2 $ssh
     * @param array|string         $commands
     */
    private static function executeCommands($ssh, $commands) {
        $output = '';
        if (is_array($commands)) {
            foreach ($commands as $command) {
                if (!empty($command)) {
                    $output = '';
                    $ssh->exec($command, function ($outputLine) use (&$output) {
                        $outputLine = self::removeAnsiControlChars($outputLine);
                        $output .= $outputLine;
                    });
                    if ($ssh->getExitStatus() !== 0) {
                        // 执行失败
                        return [
                            'result'  => false,
                            'output'  => $output,
                            'command' => $command,
                        ];
                    }
                }
            }
        } elseif (!empty($commands)) {
            $output = $ssh->exec($commands);
        }

        return [
            'result' => true,
            'output' => $output,
        ];
    }

    public static function removeAnsiControlChars($text) {
        return preg_replace('/\e[[][A-Za-z0-9.;?]*[a-zA-Z]/', '', $text);
    }

    /**
     * 删除封禁nginx黑名单
     *
     * @param $siteName
     *
     */
    public function delBanStrList($siteName) {
        $server_id = Site::query()->where("name", $siteName)->value("server_id");
        if (empty($server_id)) {
            return true;
        }
        $server_info = Server::find($server_id);
        if (empty($server_info)) {
            return true;
        }
        $ssh_host = $server_info['ip'];
        $username = $server_info['username'];
        $password = $server_info['password'];
        $siteNameList[] = $siteName;
        $siteNginxConfList = SiteNginxConf::query()->whereIn("name", $siteNameList)->get()->toArray();
        foreach ($siteNginxConfList as $siteNginxConfInfo) {
            $site = $siteNginxConfInfo['name'];
            tenancy()->initialize($site);
            //连接远程服务器
            $ssh = new SSH2($ssh_host);
            if (!$ssh->login($username, $password)) {
                return [
                    'result' => false,
                    'output' => trans('lang.server_login_fail'),
                ];
            }
            $ssh->setTimeout(600);
            $temp_file_path = $siteNginxConfInfo['access_ban_conf_path'];
            $ban_str_list = NginxBanList::query()->where('service_type', 2)->pluck('ban_str')->toArray();
            $banStr = '';
            foreach ($ban_str_list as $forBanStr){
                $banStr  .= $forBanStr . PHP_EOL;
            }
            //$banStr = $this->handlerDiffBanStr($temp_file_path, $banStrList);
            $echo_sh_commands = "echo '{$banStr}' > {$temp_file_path}";
            $execute_reload_res = $this->executeCommands($ssh, $echo_sh_commands);
            $nginx_reload_commands = 'sh /www/wwwroot/nginx_shell/nginx_reload.sh';
            $execute_reload_res = $this->executeCommands($ssh, $nginx_reload_commands);
            \Log::error('重启结果:'.json_encode($execute_reload_res).'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
        }
    }

    public function handlerDiffBanStr($temp_file_path, $banStrList) {
        $conf_ban_str = file_get_contents($temp_file_path);
        foreach ($banStrList as $banStr) {
            $conf_ban_str = str_replace($banStr, '', $conf_ban_str);
        }

        return $conf_ban_str;
    }
}
