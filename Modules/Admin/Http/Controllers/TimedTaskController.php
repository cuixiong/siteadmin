<?php

namespace Modules\Admin\Http\Controllers;

use App\Services\RabbitmqService;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\Site;
use Modules\Admin\Http\Models\TimedTask;
use phpseclib3\Net\SSH2;

class TimedTaskController extends CrudController
{
    public function store(Request $request)
    {
        // $this->DoTimedTask(['data' => ['id'=>2,'action' => 'add']]);
        // return false;
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            // 定义日志文件路径
            $input['log_path'] = $log_path = '/www/wwwroot/yadmin/admin/'.time() . rand(10000, 99999).'.log 2>&1';
            $command = $this->CreateCommand($input['type'],$input['content'],$log_path);
            if($command == false){
                ReturnJson(FALSE, trans('lang.add_error'));
            }
            $CronTime = $this->CrateLiunxTime($input['time_type'],$input['day'],$input['hour'],$input['minute'],$input['week_day']);
            $command = $CronTime.$command;// 组合命令
            $input['command'] = $command;
            // var_dump($command);die;
            // 事务开启
            DB::beginTransaction();
            try {
                $record = (new TimedTask())->create($input);
                if (!$record) {
                    DB::rollBack();
                    ReturnJson(FALSE, trans('lang.add_error'));
                }
                if(!empty($input['site_id']) && $input['category'] == 'index'){
                    $childrenTask = [];
                    foreach (explode(',', $input['site_id']) as $key => $value) {
                        $childrenTask[] = [
                            'parent_id' => $record->id,
                            'name' => $input['name'],
                            'site_id' => $value,
                            'type' => $input['type'],
                            'content' => $input['content'],
                            'status' => $input['status'],
                            'day' => $input['day'],
                            'hour' => $input['hour'],
                            'minute' => $input['minute'],
                            'week_day' => $input['week_day'],
                            'command' => $command,
                            'category' => $input['category'],
                            'time_type' => $input['time_type'],
                            'log_path' => $log_path,// 定义日志文件路径
                        ];
                    }
                    $record = $this->ModelInstance()->insert($childrenTask);
                    if (!$record) {
                        DB::rollBack();
                        ReturnJson(FALSE, trans('lang.add_error'));
                    }
                    DB::commit();
                } else {
                    $childrenTask = [
                        'parent_id' => $record->id,
                        'name' => $input['name'],
                        'site_id' => "",
                        'type' => $input['type'],
                        'content' => $input['content'],
                        'status' => $input['status'],
                        'day' => $input['day'],
                        'hour' => $input['hour'],
                        'minute' => $input['minute'],
                        'week_day' => $input['week_day'],
                        'command' => $command,
                        'category' => $input['category'],
                        'time_type' => $input['time_type'],
                        'log_path' => $log_path,// 定义日志文件路径
                    ];
                    $record = (new TimedTask())->create($childrenTask);
                    DB::commit();
                    $res = $this->TimedTaskQueue([$record->id],'add');
                }
            } catch (\Exception $e) {
                DB::rollBack();
                ReturnJson(FALSE, $e->getMessage());
            }
            // 执行命令
            ReturnJson(TRUE, trans('lang.add_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    public function CreateCommand($type, $content,$log_path)
    {
        // 根据类型进行生成liunx命令
        if($type == 'shell'){
            return ' '.$content . "  >> " .$log_path;
        } else if($type == 'http'){
            return ' curl '.$content . " >> " .$log_path;
        } else {
            return false;
        }
    }

    public function TimedTaskQueue($ids,$action)
    {
        $RabbitMQ = new RabbitmqService();
        $RabbitMQ->setQueueName('timed_task');
        foreach ($ids as $id) {
            $RabbitMQ->SimpleModePush('Modules\Admin\Http\Controllers\TimedTaskController','DoTimedTask',['id' => $id, 'action' => $action]);
        }
        $RabbitMQ->close();
    }

    public function CrateLiunxTime($timeType,$day,$hour,$minute,$week_day)
    {
        // 根据时间类型进行设置定时任务时间规则
        switch ($timeType) {
            case 'every_day':// 每天
                $CronTime = "$minute $hour * * *";
            break;
            case 'N_days':// N天
                $CronTime = "0 $minute $hour */$day *";
            break;
            case 'Every_hour':// 每小时
                $CronTime = "0 $minute * * *";
            break;
            case 'N_hours':// N小时
                $CronTime = "0 0/$minute $hour-4,8 * *";
            break;
            case 'N_minutes':// N分钟
                $CronTime = "*/$minute * * * *";
            break;
            case 'Every_week':// 每星期
                $CronTime = "$hour $minute */$week_day * *";
            break;
            case 'monthly':// 每月
                $CronTime = "$hour $minute */1 $day */1";
            break;
            default:
                return false;
            break;
        }
        return $CronTime;
    }

    public function DoTimedTask($params = null)
    {
        try {
            if($params){
                $params = $params['data'];
                file_put_contents('test.txt', "\r".json_encode($params), FILE_APPEND);
                $task = TimedTask::find($params['id']);
                file_put_contents('test.txt', "\r".json_encode($task), FILE_APPEND);
                if($task->category == 'admin'){
                    $params['command'] = '';
                    $this->LocalHostTask($params['action'],$task->command,$params['command']);
                // } else if($task->category == 'index') {
                //     $site = Site::find($params['site_id']);
                //     $this->ShhTask($site->ip,$site->username,$site->password,$params['action'],$task->command,$params['command']);
                }
                return true;
            }
        } catch (\Exception $e) {
            file_put_contents('error_log.txt', "\r".json_encode($e->getMessage()), FILE_APPEND);
            return false;
        }
    }

    public function LocalHostTask($doAction,$command,$OldCommand = '')
    {
        try {
            $taskList = shell_exec('crontab -l');
            if($doAction == 'add'){
                $taskListArr = array_filter(explode('\n',$taskList));
                if (!in_array($command, $taskListArr)){
                    $command = 'echo "'.trim($command).'" | crontab -';
                    shell_exec($command);
                }
            } else if($doAction == 'update') {
                $taskList = str_replace($OldCommand, $command, $taskList);
                $result = shell_exec('echo "'.trim($taskList).'" | crontab -');
                if($result === null){
                    return true;
                } else {
                    return false;
                }
            } else if($doAction == 'delete') {
                $taskList = str_replace($OldCommand, '', $taskList);
                $result = shell_exec('echo "'.trim($taskList).'" | crontab -');
                if($result === null){
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            file_put_contents('error_log.txt', "\r".json_encode($e->getMessage()), FILE_APPEND);
            return false;
        }
    }

    public function ShhTask($ip,$username,$password,$doAction,$command,$OldCommand = '')
    {
        $ssh = new SSH2($ip);
        $res = $ssh->login($username,$password);
        if(!$res){
            return false;
        }
        $taskList = $ssh->exec('crontab -l');
        if($doAction == 'add'){
            $taskListArr = array_filter(explode('\n',$taskList));
            if (!in_array($command, $taskListArr)){
                $command = 'echo "'.trim($command).'" | crontab -';
                $ssh->exec($command);
            }
        } else if($doAction == 'update') {
            $taskList = str_replace($OldCommand, $command, $taskList);
            $result = $ssh->exec('echo "'.trim($taskList).'" | crontab -');
            if($result === null){
                return true;
            } else {
                return false;
            }
        } else if($doAction == 'delete') {
            $taskList = str_replace($OldCommand, '', $taskList);
            $result = $ssh->exec('echo "'.trim($taskList).'" | crontab -');
            if($result === null){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
