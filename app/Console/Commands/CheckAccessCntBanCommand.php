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
    public $now_time = 0;

    public $banIpContent = [];

    public function __construct() {
        parent::__construct();
        $this->now_time = time();
    }

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
//            \Log::error(
//                '检测nginx负载异常:'.json_encode([$e->getMessage()]).'  文件路径:'.__CLASS__.'  行号:'.__LINE__
//            );
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
        //获取是否封禁4段ip的配置
        $ban_full_ip_status = $sysValList['ban_full_ip_status']['hidden'];
        $ban_full_ip_cnt = $sysValList['ban_full_ip_cnt']['value'] ?? 50;
        if (!empty($ban_full_ip_status) && $tab != 'ip') {
            $use_full_ip_status = true;
        } else {
            $use_full_ip_status = false;
        }

        $ban_rule_list = [];
        foreach ($sysValList as $forkey => $forData){
            if (strpos($forkey, 'ban_rules') !== false) {
                $ban_rule_list[$forkey] = $forData;
            }
        }

        $accessIpLogList = [];
        $ban_full_ip_list = [];
        foreach ($ban_rule_list as $ban_rule_key => $ban_rule_val){
            $unit = $ban_rule_val['value'];
            $unit_max_cnt = $ban_rule_val['back_value'] ?? 0;
            $start_time = $this->now_time - $unit * 60;
            //$start_time = 0;
            if (empty($unit_max_cnt) || $unit_max_cnt <= 0) {
                continue;
            } else {
                $forAccessIpLogList = AccessLog::query()
                                               ->whereBetween('created_at', [$start_time, $this->now_time])
                                               ->groupBy($tab)
                                               ->selectRaw("count(*) as cnt, ".$tab)
                                               ->having('cnt', '>=', $unit_max_cnt)
                                               ->pluck('cnt', $tab)->toArray();
                if(!empty($forAccessIpLogList )){
                    $accessIpLogList = array_merge($accessIpLogList, $forAccessIpLogList);
                }

                if (!empty($forAccessIpLogList) && $use_full_ip_status) {
                    $tap_ip_list = array_keys($forAccessIpLogList);
                    $forAccessIpLogList = AccessLog::query()
                                                 ->whereBetween('created_at', [$start_time, $this->now_time])
                                                 ->whereIn($tab, $tap_ip_list)
                                                 ->groupBy('ip')
                                                 ->selectRaw("count(*) as cnt, ip")
                                                 ->having('cnt', '>=', $ban_full_ip_cnt)
                                                 ->pluck('cnt', 'ip')
                                                 ->toArray();
                    $ban_full_ip_list = array_merge($ban_full_ip_list, $forAccessIpLogList);
                }
                $this->addContentByIp($forAccessIpLogList, $use_full_ip_status, $unit_max_cnt, $tab , $unit);
            }
        }

        if ($use_full_ip_status) {
            foreach ($ban_full_ip_list as $forIp => $forVal) {
                $ipstr = 'deny '.$forIp.";";
                $banIpStrList[] = PHP_EOL.$ipstr;
            }
        } else {
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
        }
        $banStr = '';
        if (!empty($banIpStrList)) {
            $banIpStrList = array_unique($banIpStrList);
            $banStr = implode('', $banIpStrList);
        }
        $banStr .= PHP_EOL;

        return $banStr;
    }

    /**
     *
     * @param $ip_list
     * @param $use_full_ip_status
     * @param $max_cnt
     * @param $tab
     * @param $unit
     *
     */
    public function addContentByIp($ip_list, $use_full_ip_status, $max_cnt, $tab , $unit) {
        foreach ($ip_list  as $forIp => $forCnt){
            $forStr = '';
            if($use_full_ip_status) {
                $for_ip_arr = explode('.', $forIp);
                if ($tab == 'ip_muti_second') {
                    $for_temp_ip = $for_ip_arr[0].".".$for_ip_arr[1]."(开启封禁完整ip配置)";
                } elseif ($tab == 'ip_muti_third') {
                    $for_temp_ip = $for_ip_arr[0].".".$for_ip_arr[1].".".$for_ip_arr[2]."(开启封禁完整ip配置)";
                } else {
                    $for_temp_ip = $forIp;
                }
            }else{
                if ($tab == 'ip_muti_second') {
                    $for_temp_ip = $forIp."(开启2段ip封禁)";;
                } elseif ($tab == 'ip_muti_third') {
                    $for_temp_ip = $forIp."(开启3段ip封禁)";;
                } else {
                    $for_temp_ip = $forIp."(开启4段ip封禁)";;
                }
            }

            $forStr.= "{$unit}分钟内 {$for_temp_ip} 访问超过 {$max_cnt} 次 ({$forCnt}次)";

            $this->banIpContent[$forIp] = $forStr;
        }
    }

    public function getContentByBanStr($banStr) {
        $content = '';
        foreach ($this->banIpContent as $forip => $forStr){
            if(strpos($banStr, $forip) !== false){
                $content = $forStr;
                break;
            }
        }
        return $content;
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
//            \Log::error('重启nginx失败:'.json_encode($output));
        } else {
//            \Log::error('重启nginx成功:'.json_encode($output));
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
                $content = $this->getContentByBanStr($forbanStr);
                $forRealBanStr = $forbanStr.";";
                $add_data = [
                    'ban_str'      => $forRealBanStr,
                    'ban_type'     => 1,
                    'service_type' => 2,
                    'content'      => $content,
                    'created_at'   => $this->now_time,
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
            foreach ($ban_str_list as $forBanStr) {
                $banStr .= $forBanStr.PHP_EOL;
            }
            //$banStr = $this->handlerDiffBanStr($temp_file_path, $banStrList);
            $echo_sh_commands = "echo '{$banStr}' > {$temp_file_path}";
            $execute_reload_res = $this->executeCommands($ssh, $echo_sh_commands);
            $nginx_reload_commands = 'sh /www/wwwroot/nginx_shell/nginx_reload.sh';
            $execute_reload_res = $this->executeCommands($ssh, $nginx_reload_commands);
//            \Log::error('重启结果:'.json_encode($execute_reload_res).'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
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
