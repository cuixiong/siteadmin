<?php
/**
 * ProductSetDataCommand.php UTF-8
 * 游戏设置数据命令行
 *
 * @date    : 2024/6/20 11:27 上午
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
use Modules\Site\Http\Controllers\SiteCrontabController;
use Modules\Site\Http\Models\AccessLog;
use Modules\Site\Http\Models\NginxBanList;
use Modules\Site\Http\Models\System as SiteSystem;
use Modules\Site\Http\Models\SystemValue;
use Modules\Admin\Http\Models\SystemValue as AdminSystemValue;
use phpseclib3\Net\SSH2;

class CheckNginxLoadCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:check_nginx_load';
    public $os_info_str = '';

    public function handle() {
        try {
            //设置日志
            config(['logging.default' => 'cli']);
            $script_path = 'sh /www/wwwroot/nginx_shell/new_query_os_load.sh';
            exec($script_path, $load_os_arr);
            $load_os_data = current($load_os_arr);
            $load_os_val = explode(":", $load_os_data)[1] ?? 0;
            //获取宽带使用率
            $net_usage_rate_script_path = 'sh /www/wwwroot/bandwidth_util.sh';
            exec($net_usage_rate_script_path, $load_net_usage);
            $load_net_usage_data = current($load_net_usage);
            $net_usage_val = explode(":", $load_net_usage_data)[1] ?? 0;
//            $siteNameList = Site::query()->where("status", 1)
//                                ->where("is_loc!al", 1)
//                                ->pluck("name");
//            $siteNameList = ['168report'];
            $is_overseas = env('is_overseas', 1);
            $siteNginxConfList = SiteNginxConf::query()
                                              ->where("is_overseas", $is_overseas)
                                              ->get()->toArray();
            foreach ($siteNginxConfList as $siteNginxConfInfo) {
                $site = $siteNginxConfInfo['name'];
                tenancy()->initialize($site);
                //$sysValList = SystemValue::query()->where("alias", 'nginx_ban_rules')->pluck('value', 'key')->toArray();
                $parent_id = SiteSystem::query()->where("alias", 'nginx_ban_rules')
                                       ->value("id");
                $sysValList = SystemValue::query()->where("parent_id", $parent_id)
                                         ->get()->keyBy("key")->toArray();
                $check_max_load = $sysValList['check_max_load']['value'] ?? 80;
                $check_min_load = $sysValList['check_min_load']['value'] ?? 60;
                $net_usage_rate = $sysValList['net_usage_rate']['value'] ?? 90;
                $net_usage_val = rtrim($net_usage_val, '%');
                $this->os_info_str = '';
                echo "当前时间:".date("Y-m-d H:i:s")
                     ."  服务器负载:{$load_os_val}  最大负载:{$check_max_load}  最小负载:{$check_min_load}   网络使用率:{$net_usage_val}  网络最高使用率:{$net_usage_rate}"
                     .PHP_EOL;
                if ($load_os_val >= $check_max_load || $net_usage_val >= $net_usage_rate) {
                    if($load_os_val >= $check_max_load){
                        $this->os_info_str .= "当前服务器负载:{$load_os_val}已超过配置最大负载:{$check_max_load} ";
                    }
                    if($net_usage_val >= $net_usage_rate){
                        $this->os_info_str .= "当前服务器网络使用率:{$net_usage_val}已超过配置网络使用率:{$net_usage_rate} ";
                    }
                    $banStr = $this->getBanNginxStr($siteNginxConfInfo, $sysValList);
//                    \Log::error(
//                        "{$siteNginxConfInfo['name']}:nginx封禁字符串:{$banStr}".'  文件路径:'.__CLASS__.'  行号:'
//                        .__LINE__
//                    );
                    echo "{$siteNginxConfInfo['name']}:nginx封禁字符串:{$banStr}".PHP_EOL;
                    $this->writeNginxConf($banStr, $siteNginxConfInfo);
                    $this->reloadNginx();
                } elseif ($load_os_val < $check_min_load) {
                    //小于最低负载
                    //恢复nginx配置
                    $banStr = $this->getBlackBanNginxStr($sysValList);
                    echo '黑名单封禁:'.$banStr.PHP_EOL;
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
    public function getBanNginxStr($siteNginxConfInfo, $sysValList) {
        if (empty($siteNginxConfInfo['conf_temp_path']) || empty($siteNginxConfInfo['conf_real_path'])) {
            return false;
        }
        //获取当前站点, 异常流量
        $beforeIpTime = $sysValList['beforeIpTime']['value'] ?? 5; //默认5分钟
        $ipMaxCnt = $sysValList['ipMaxReqCnt']['value'] ?? 100; //默认100次
        $ipMutiType = $sysValList['ipMutiType']['value'] ?? 3; //默认3段ip
        if ($ipMutiType == 2) {
            $tab = 'ip_muti_second';
        } elseif ($ipMutiType == 3) {
            $tab = 'ip_muti_third';
        } else {
            $tab = 'ip';
        }
        //获取是否封禁4段ip的配置
        $ban_full_ip_status = $sysValList['os_ban_full_ip_status']['hidden'];
        $ban_full_ip_cnt = $sysValList['os_ban_full_ip_cnt']['value'] ?? 50;
        if (!empty($ban_full_ip_status) && $tab != 'ip') {
            $use_full_ip_status = true;
        } else {
            $use_full_ip_status = false;
        }
        $nowtime = time();
        $start_time = $nowtime - $beforeIpTime * 60;
        $accessIpLogList = AccessLog::query()
                                    ->whereBetween('created_at', [$start_time, $nowtime])
                                    ->groupBy($tab)
                                    ->selectRaw("count(*) as cnt, ".$tab)
                                    ->having('cnt', '>=', $ipMaxCnt)
                                    ->pluck('cnt', $tab)->toArray();
        if (!empty($accessIpLogList) && $use_full_ip_status) {
            $tap_ip_list = array_keys($accessIpLogList);
            $accessIpLogList = AccessLog::query()
                                           ->whereBetween('created_at', [$start_time, $nowtime])
                                           ->whereIn($tab, $tap_ip_list)
                                           ->groupBy('ip')
                                           ->selectRaw("count(*) as cnt, ip")
                                           ->having('cnt', '>=', $ban_full_ip_cnt)
                                           ->pluck('cnt', 'ip')
                                           ->toArray();
        }
        $banStr = '';
        $banIpStrList = [];
        //查询超过1次的
        $black_ban_cnt = $sysValList['black_ban_cnt']['value'] ?? 1;
        $cntBlackIpList = NginxBanList::query()->where("ban_type", 1)
                                      ->where("status", 1)
                                      ->where("service_type", 1)
                                      ->groupBy('ban_str')
                                      ->having('cnt', '>=', $black_ban_cnt)
                                      ->selectRaw('count(*) as cnt, ban_str')
                                      ->pluck('ban_str')->toArray();
        if (!empty($cntBlackIpList)) {
            foreach ($cntBlackIpList as $forIp) {
                $banIpStrList[] = PHP_EOL.$forIp;
            }
        }
        $blackAddIpList = [];
        foreach ($accessIpLogList as $forIp => $forValCnt) {
            if($use_full_ip_status){
                $ipstr = 'deny '.$forIp.";";
            }elseif ($tab == 'ip_muti_second') {
                $ipstr = 'deny '.$forIp.'.0.0/16;';
            } elseif ($tab == 'ip_muti_third') {
                $ipstr = 'deny '.$forIp.'.0/24;';
            } else {
                $ipstr = 'deny '.$forIp.";";
            }
            $content = $this->getContentByIp($sysValList, $forIp, $forValCnt, $use_full_ip_status);
            $blackAddIpList[] = [
                'ban_str'    => $ipstr,
                'content'    => $content,
                'ban_type'   => 1,
                'created_at' => $nowtime,
            ];
            $banIpStrList[] = PHP_EOL.$ipstr;
        }
        NginxBanList::query()->insert($blackAddIpList);
        if (!empty($banIpStrList)) {
            $banIpStrList = array_unique($banIpStrList);
            $banStr = implode('', $banIpStrList);
        }
        ########################################################################
        //查询超过1次的UA
        $cntBlackUaList = NginxBanList::query()->where("ban_type", 2)
                                      ->where("status", 1)
                                      ->where("service_type", 1)
                                      ->groupBy('ban_str')
                                      ->having('cnt', '>=', $black_ban_cnt)
                                      ->selectRaw('count(*) as cnt, ban_str')
                                      ->pluck('ban_str')->toArray();
        $banUaStrList = [];
        $UaMaxCnt = $sysValList['uaMaxReqCnt']['value'] ?? 100; //默认100次
        $beforeUaTime = $sysValList['beforeUaTime']['value'] ?? 5; //默认5分钟
        $ua_start_time = $nowtime - $beforeUaTime * 60;
        $accessUaLogList = AccessLog::query()->where("created_at", ">", $ua_start_time)
                                    ->groupBy('ua_info')
                                    ->selectRaw("count(*) as cnt, ua_info")
                                    ->having('cnt', '>=', $UaMaxCnt)
                                    ->pluck('cnt', 'ua_info')->toArray();
        //插入黑名单
        $blackUaList = [];
        foreach ($accessUaLogList as $forAccUaVal => $forAccUaCnt) {
            $content = $this->getContentByUa($sysValList, $forAccUaVal , $forAccUaCnt);
            $blackUaList[] = [
                'ban_str'    => $forAccUaVal,
                'content'    => $content,
                'ban_type'   => 2,
                'created_at' => $nowtime,
            ];
        }
        NginxBanList::query()->insert($blackUaList);
        //合并UA列表
        $accessUaLogList = array_keys($accessUaLogList);
        $banUaStrList = array_merge($accessUaLogList, $cntBlackUaList);
        if (!empty($banUaStrList)) {
            $banUaStrList = array_unique($banUaStrList);
            $uabanStr = PHP_EOL.'if ($http_user_agent ~* "';
            $uaListStr = '';
            foreach ($banUaStrList as $forUaVal) {
                $handler_ua = "(".$this->customEscape($forUaVal, ['.', '(', ')', '+', '?', "*", '\\']).")|";
                $uaListStr .= $handler_ua;
            }
            $uabanStr .= rtrim($uaListStr, '|');
            $uabanStr .= '") {';
            $uabanStr .= PHP_EOL.'return 403;'.PHP_EOL;
            $uabanStr .= '}'.PHP_EOL;
        }
        if (!empty($uabanStr)) {
            $banStr .= PHP_EOL.$uabanStr;
        }
        $banStr .= PHP_EOL;

        return $banStr;
        //$banStr = "";
        //字符串替换
        //$this->writeNginxConf($banStr, $siteNginxConfInfo);
    }

    public function getContentByIp($sysValList, $forIp, $forValCnt ,$use_full_ip_status) {
        //获取当前站点, 异常流量
        $beforeIpTime = $sysValList['beforeIpTime']['value'] ?? 5; //默认5分钟
        $ipMaxCnt = $sysValList['ipMaxReqCnt']['value'] ?? 100; //默认100次
        $ipMutiType = $sysValList['ipMutiType']['value'] ?? 3; //默认3段ip
        if($use_full_ip_status){
            $ip_msg_str = '当前已开启全IP封禁';
        }else{
            $ip_msg_str = "当前开启{$ipMutiType}段IP校验";
        }
        return $this->os_info_str." , $ip_msg_str {$forIp} 在{$beforeIpTime}分钟内访问超过{$ipMaxCnt}次 ({$forValCnt}次)";
    }

    public function getContentByUa($sysValList, $forAccUaVal, $forAccUaCnt) {
        //获取当前站点, 异常流量
        $beforeUaTime = $sysValList['beforeUaTime']['value'] ?? 5; //默认5分钟
        $UaMaxCnt = $sysValList['uaMaxReqCnt']['value'] ?? 100; //默认100次

        return $this->os_info_str." 当前UA校验, {$forAccUaVal}在{$beforeUaTime}分钟内访问超过{$UaMaxCnt}次 ({$forAccUaCnt}次)";
    }

    public function getBlackBanNginxStr($sysValList) {
        //查询超过N次的IP
        $black_ban_cnt = $sysValList['black_ban_cnt']['value'] ?? 1;
        $cntBlackIpList = NginxBanList::query()->where("ban_type", 1)
                                      ->where("status", 1)
                                      ->where("service_type", 1)
                                      ->groupBy('ban_str')
                                      ->having('cnt', '>=', $black_ban_cnt)
                                      ->selectRaw('count(*) as cnt, ban_str')
                                      ->pluck('ban_str')->toArray();
        if (!empty($cntBlackIpList)) {
            foreach ($cntBlackIpList as $forIp) {
                $banIpStrList[] = PHP_EOL.$forIp;
            }
        }
        $banStr = '';
        if (!empty($banIpStrList)) {
            $banIpStrList = array_unique($banIpStrList);
            $banStr = implode('', $banIpStrList);
        }
        //查询超过N次的UA
        $banUaStrList = NginxBanList::query()->where("ban_type", 2)
                                    ->where("status", 1)
                                    ->where("service_type", 1)
                                    ->groupBy('ban_str')
                                    ->having('cnt', '>=', $black_ban_cnt)
                                    ->selectRaw('count(*) as cnt, ban_str')
                                    ->pluck('ban_str')->toArray();
        if (!empty($banUaStrList)) {
            $banUaStrList = array_unique($banUaStrList);
            $uabanStr = PHP_EOL.'if ($http_user_agent ~* "';
            $uaListStr = '';
            foreach ($banUaStrList as $forUaVal) {
                $handler_ua = "(".$this->customEscape($forUaVal, ['.', '(', ')', '+', '?', "*", '\\']).")|";
                $uaListStr .= $handler_ua;
            }
            $uabanStr .= rtrim($uaListStr, '|');
            $uabanStr .= '") {';
            $uabanStr .= PHP_EOL.'return 403;'.PHP_EOL;
            $uabanStr .= '}'.PHP_EOL;
        }
        if (!empty($uabanStr)) {
            $banStr .= PHP_EOL.$uabanStr;
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
//        $temp_content = file_get_contents($siteNginxConfInfo['conf_temp_path']);
//        if (empty($temp_content)) {
//            return false;
//        }
//        $modifiedString = str_replace("#DynamicBanSet", $banStr, $temp_content);
        $new_file_path = $siteNginxConfInfo['conf_temp_path'];
        file_put_contents($new_file_path, $banStr);
    }

    public function reloadNginxBySite($siteName) {
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
            $parent_id = SiteSystem::query()->where("alias", 'nginx_ban_rules')
                                   ->value("id");
            $sysValList = SystemValue::query()->where("parent_id", $parent_id)
                                     ->get()->keyBy("key")->toArray();
            //$banStr = $this->getBanNginxStr($siteNginxConfInfo, $sysValList);
            $banStr = $this->getBlackBanNginxStr($sysValList);
//            \Log::error(
//                "{$siteNginxConfInfo['name']}:nginx封禁字符串:{$banStr}".'  文件路径:'.__CLASS__.'  行号:'
//                .__LINE__
//            );
            //$this->writeNginxConf($banStr, $siteNginxConfInfo); //open_basedir restriction in effect.
            //连接远程服务器
            $ssh = new SSH2($ssh_host);
            if (!$ssh->login($username, $password)) {
                return [
                    'result' => false,
                    'output' => trans('lang.server_login_fail'),
                ];
            }
            $ssh->setTimeout(600);
//            $temp_content = file_get_contents($siteNginxConfInfo['conf_temp_path']);
//            if (empty($temp_content)) {
//                return false;
//            }
//            $modifiedString = str_replace("#DynamicBanSet", $banStr, $temp_content);
            //$temp_file_path = "/www/wwwroot/nginx_shell/temp_site_{$site}_nginx.conf";
            //file_put_contents($temp_file_path, $banStr);
            $temp_file_path = $siteNginxConfInfo['conf_temp_path'];
            $echo_sh_commands = "echo '{$banStr}' > {$temp_file_path}";
            $execute_reload_res = $this->executeCommands($ssh, $echo_sh_commands);
            //$this->reloadNginx();
            $nginx_reload_commands = 'sh /www/wwwroot/nginx_shell/nginx_reload.sh';
            $execute_reload_res = $this->executeCommands($ssh, $nginx_reload_commands);
//            \Log::error('重启结果:'.json_encode($execute_reload_res).'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
        }
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
}
