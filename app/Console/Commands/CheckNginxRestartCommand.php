<?php
/**
 * CheckNginxRestartCommand.php UTF-8
 * 检查nginx重启
 *
 * @date    : 2025/1/23 14:44 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Admin\Http\Models\SiteNginxConf;
use Modules\Site\Http\Models\SystemValue;

class CheckNginxRestartCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:check_nginx_load_restart';

    public function handle() {
        try {
            //设置日志
            config(['logging.default' => 'cli']);
            $script_path = 'sh /www/wwwroot/nginx_shell/new_query_os_load.sh';
            exec($script_path, $load_os_arr);
            $load_os_data = current($load_os_arr);
            $load_os_val = explode(":", $load_os_data)[1] ?? 0;
            if($load_os_val >= 90){
                $restart_script_path = 'sh /www/wwwroot/nginx_shell/nginx_restart.sh';
                exec($restart_script_path, $restart_arr);
                $msg = implode(',', $restart_arr);
                echo "当前时间:".date("Y-m-d H:i:s")."服务器负载率{$load_os_val}, 重启nginx!".$msg.PHP_EOL;
            }else{
                echo "当前时间:".date("Y-m-d H:i:s")."服务器负载率{$load_os_val}, 正常!".PHP_EOL;
            }
        } catch (\Exception $e) {
            \Log::error(
                '检测nginx负载异常:'.json_encode([$e->getMessage()]).'  文件路径:'.__CLASS__.'  行号:'.__LINE__
            );
            echo $e->getMessage().PHP_EOL;
            exit;
        }
    }
}
