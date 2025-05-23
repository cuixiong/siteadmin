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
        if(!empty($slave_info_arr[0])) {
            $slave_info = $slave_info_arr[0];
            if ($slave_info->Slave_IO_Running != 'Yes' || $slave_info->Slave_SQL_Running != 'Yes') {
                $scene = EmailScene::where('action', 'register')->select(
                    ['id', 'name', 'title', 'body', 'email_sender_id', 'email_recipient', 'status', 'alternate_email_id']
                )->first();
                $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
                    $scene->email_sender_id
                );
                $scene->title = '主从同步异常';
                $scene->body = '数据库主从同步异常';
                $email = '798396652@qq.com';
                (new SendEmailController())->handlerSendEmail($scene, $email, [], $senderEmail , true);

                echo "数据库主从同步异常".PHP_EOL;
            } else {
                echo "数据库主从同步正常".PHP_EOL;
            }
        }
    }
}
