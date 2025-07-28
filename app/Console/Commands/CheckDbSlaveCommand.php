<?php

namespace App\Console\Commands;

use App\Http\Controllers\SendEmailController;
use App\Mail\TrendsEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Admin\Http\Models\Email;
use Modules\Admin\Http\Models\EmailScene;
use Modules\Site\Http\Controllers\SyncThirdProductController;
use phpseclib3\Math\BigInteger\Engines\PHP;

class CheckDbSlaveCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:checkdbslave';

    public function handle() {
        $slave_info_arr = DB::select('SHOW SLAVE STATUS');
        $is_overseas = env('is_overseas');
        if(!in_array($is_overseas, [2 ,3])){
            echo "当前环境无需检测数据库同步异常".PHP_EOL;
            return true;
        }
        if (!empty($slave_info_arr[0])) {
            $slave_info = $slave_info_arr[0];
            if ($slave_info->Slave_IO_Running != 'Yes' || $slave_info->Slave_SQL_Running != 'Yes') {
                $scene = EmailScene::where('action', 'db_sync_error')->select(
                    ['id', 'name', 'title', 'body', 'email_sender_id', 'email_recipient', 'status',
                     'alternate_email_id']
                )->first();
                $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
                    $scene->email_sender_id
                );
                $err_msg = '';
                if($is_overseas == 2){
                    $err_msg .= "硅谷服务器(IP:47.254.43.120)主从同步异常, 错误信息为:";
                }elseif ($is_overseas == 3){
                    $err_msg .= "日本服务器(IP:8.211.148.26)主从同步异常, 错误信息为:";
                }
                $err_msg .= $slave_info->Last_SQL_Error ?? '';
                $data['error_message'] = $err_msg;
                $data['domain'] = env('APP_DOMAIN', '');
                // 收件人的数组
                $emails = explode(',', $scene->email_recipient);
                foreach ($emails as $email) {
                    (new SendEmailController())->handlerSendEmail($scene, $email, $data, $senderEmail, true);
                }
                echo "数据库主从同步异常".PHP_EOL;
            } else {
                echo "数据库主从同步正常".PHP_EOL;
            }
        }
    }
}
