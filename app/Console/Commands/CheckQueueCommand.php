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
use Modules\Admin\Http\Models\System;
use Modules\Admin\Http\Models\SystemValue;

class CheckQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:queue-status';

    public function handle()
    {
        $parentId = System::query()->where("alias", 'check_queue_status_list')->value("id");
        $queueNameList = SystemValue::query()->where("parent_id", $parentId)
            ->get();
        $queueNameData = [];
        if ($queueNameList) {
            $queueNameList = $queueNameList->toArray();
            foreach ($queueNameList as $key => $item) {
                $tempArray = [];
                $tempArray = explode("\n", $item['value'] ?? '');
                $queueNameData = array_merge($queueNameData, $tempArray);
            }
            $queueNameData = array_filter($queueNameData);
        }

        // 执行命令获取supervisor状态 (宝塔)
        $cmd = '/www/server/panel/pyenv/bin/supervisorctl -c /etc/supervisor/supervisord.conf status';
        $output = shell_exec($cmd);

        $errorQueue = [];
        $lines = explode("\n", trim($output));
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            // 解析格式 格式参考："lpijp_queue:lpijp_queue_00 RUNNING pid 2617245, uptime 5:51:01"
            preg_match('/^(\S+)\s+(\S+)(?:\s+pid\s+(\d+))?(?:,\s+uptime\s+(.+))?/', $line, $matches);

            $name = $matches[1] ?? '';
            if (empty($name)) {
                continue;
            }
            $queueNamePrefix = explode(':', $name);
            $queueNamePrefix = $queueNamePrefix[0];
            $queueStatus  = $matches[2];

            // 登记的队列是否出现故障 STOPPED FATAL
            if (in_array($queueNamePrefix, $queueNameData) && $queueStatus == 'FATAL') {
                $errorQueue[] = $name;
            }
        }
        // dd($errorQueue);

        if(count($errorQueue)>0){
            
            $scene = EmailScene::where('action', 'check_queue_status_fatal')->select(
                ['id', 'name', 'title', 'body', 'email_sender_id', 'email_recipient', 'status',
                 'alternate_email_id']
            )->first();
            $senderEmail = Email::select(['name', 'email', 'host', 'port', 'encryption', 'password'])->find(
                $scene->email_sender_id
            );
            $err_msg = '队列出现故障'."\n";
            foreach ($errorQueue as $item) {
                # code...
                $err_msg .= $item."\n";
            }
            $data['error_message'] = $err_msg;
            $data['domain'] = env('APP_DOMAIN', '');
            // 收件人的数组
            $emails = explode(',', $scene->email_recipient);
            foreach ($emails as $email) {
                (new SendEmailController())->handlerSendEmail($scene, $email, $data, $senderEmail, true);
            }
        }else{
            echo '没有异常队列'.PHP_EOL;
        }


    }
}
