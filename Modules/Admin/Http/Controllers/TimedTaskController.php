<?php

namespace Modules\Admin\Http\Controllers;

use App\Services\RabbitmqService;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\TimedTask;
use phpseclib3\Net\SSH2;

class TimedTaskController extends CrudController
{
    public function store(Request $request)
    {
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
            // 事务开启
            DB::beginTransaction();
            try {
                $record = (new TimedTask())->create($input);
                if (!$record) {
                    DB::rollBack();
                    ReturnJson(FALSE, trans('lang.add_error'));
                }
                if($input['category'] == 'index'){
                    if(empty($input['site_id'])){
                        DB::commit();
                    } else {
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
                    }
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
            // 定义日志文件路径
            $input['log_path'] = $log_path = '/www/wwwroot/yadmin/admin/'.time() . rand(10000, 99999).'.log 2>&1';
            $command = $this->CreateCommand($input['type'],$input['content'],$log_path);
            if($command == false){
                ReturnJson(FALSE, trans('lang.add_error'));
            }
            $CronTime = $this->CrateLiunxTime($input['time_type'],$input['day'],$input['hour'],$input['minute'],$input['week_day']);
            $command = $CronTime.$command;// 组合命令
            $input['command'] = $command;
            $record = $this->ModelInstance()->findOrFail($request->id);
            // 更新父任务
            if (!$record->update($input)) {                                                                                                                                                                                                                                                
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            $data = [
                'parent_id' => $record->id,
                'name' => $record->name,
                'type' => $record->type,
                'content' => $record->content,
                'status' => $record->status,
                'day' => $record->day,
                'hour' => $record->hour,
                'minute' => $record->minute,
                'week_day' => $record->week_day,
                'command' => $record->command,
                'category' => $record->category,
                'time_type' => $record->time_type,
                'log_path' => $record->log_path,
            ];
            $childrenTasks = $this->ModelInstance()->where('parent_id',$record->id)->get()->toArray();
            // 更新子任务
            if($record->category == 'index'){
                $siteIds = explode(',', $record->site_id);
                $childrenTasks = array_column($childrenTasks,null,'site_id');
                $childrenSiteIds = array_keys($childrenTasks);
                // var_dump($childrenSiteIds);die;
                $childrenUpdateIds = array_intersect($childrenSiteIds,$siteIds);
                // var_dump($childrenUpdateIds);die;
                $childrenInsertIds = array_diff($siteIds,$childrenSiteIds,);
                // var_dump($childrenInsertIds);die;
                $childrenDeleteIds = array_diff($childrenSiteIds,$siteIds);
                // var_dump($childrenDeleteIds);die;
                
                // 子任务编辑（对应站点的定时任务）
                if(!empty($childrenUpdateIds)){
                    $childrenUpdateData = [];
                    $updateIds = [];
                    foreach ($childrenUpdateIds as $key => $id) {
                        $updateIds[] = $childrenTasks[$id]['id'];
                        $childrenUpdateData[] = array_merge(['site_id' => $id,'id' => $childrenTasks[$id]['id']],$data);
                    }
                    $this->ModelInstance()->upsert($childrenUpdateData,['id'],array_keys($childrenUpdateData[0]));
                }
                // 子任务新增（对应站点的定时任务）
                if(!empty($childrenInsertIds)){
                    $childrenInsertData = [];
                    foreach ($childrenInsertIds as $key => $id) {
                        $childrenInsertData[] = array_merge(['site_id' => $id],$data);
                    }
                    $insertIds = $this->ModelInstance()->insert($childrenInsertData);
                }
                // 子任务删除（对应站点的定时任务）
                if(!empty($childrenDeleteIds)){
                    $deleteIds = [];
                    foreach ($childrenDeleteIds as $key => $id) {
                        $deleteIds[] = $childrenTasks[$id]['id'];
                    }
                    // var_dump($deleteIds);die;
                    // $this->TimedTaskQueue($deleteIds,'delete');
                    $this->ModelInstance()->whereIn('id',$deleteIds)->delete();
                }
            } else {
                $this->ModelInstance()->where('parent_id',$record->id)->update($data);
                $task = $childrenTasks[0];
                $this->TimedTaskQueue([$task['id']],'update');
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            $childrenIds = $this->ModelInstance()->whereIn('parent_id', $ids)->pluck('id')->toArray();
            // var_dump($childrenIds);die;
            $ids = array_merge($ids, $childrenIds);
            // $res = $this->ModelInstance()->whereIn('id',$ids)->delete();
            // $res = $this->TimedTaskQueue($ids,'delete');
            $res = true;
            if($res === true){
                ReturnJson(TRUE, trans('lang.delete_success'));
            } else {
                ReturnJson(FALSE,trans('lang.delete_error'));
            }
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
        // try {
        //     $RabbitMQ = new RabbitmqService();
        //     $RabbitMQ->setQueueName('timed_task');
        //     foreach ($ids as $id) {
        //         $RabbitMQ->SimpleModePush('Modules\Admin\Http\Controllers\TimedTaskController','DoTimedTask',['id' => $id, 'action' => $action]);
        //     }
        //     $RabbitMQ->close();
        //     return true;
        // } catch (\Exception $e) {
        //     file_put_contents('error_log.txt', "\r".json_encode($e->getMessage()), FILE_APPEND);
        //     return false;
        // }
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
                $res = false;
                if($task->category == 'admin'){
                    $params['command'] = '';
                    file_put_contents('test.txt', "\r admin yes", FILE_APPEND);
                    $res = $this->LocalHostTask($params['action'],$task->command,$params['command']);
                // } else if($task->category == 'index') {
                //     $site = Site::find($params['site_id']);
                //     $this->ShhTask($site->ip,$site->username,$site->password,$params['action'],$task->command,$params['command']);
                }
                if($params['action'] == 'delete' && $res === true){
                    // 先删除子任务
                    TimedTask::where('parent_id',$params['id'])->delete();
                    // 删除自身任务
                    TimedTask::where('id',$params['id'])->delete();
                }
                file_put_contents('test.txt', "\r admin no", FILE_APPEND);
                return true;
            }
        } catch (\Exception $e) {
            file_put_contents('error_log.txt', "\r".json_encode($e->getMessage()), FILE_APPEND);
            return false;
        }
    }

    public function LocalHostTask($doAction,$command,$OldCommand = '')
    {
        file_put_contents('error_log.txt', "\r".$command."\r".$doAction."\r".$OldCommand, FILE_APPEND);
        try {
            $taskList = shell_exec('crontab -l');
            if($doAction == 'add'){
                file_put_contents('test.txt', "\r add yes", FILE_APPEND);
                $taskListArr = array_filter(explode('\n',$taskList));
                file_put_contents('test.txt', "\r".json_encode($taskListArr), FILE_APPEND);
                
                if (!in_array($command, $taskListArr)){
                    file_put_contents('test.txt', "\r add to in array yes", FILE_APPEND);
                    $command = 'echo "'.trim($command,'').'" | crontab -';
                    file_put_contents('error_log.txt', "\r".$command, FILE_APPEND);
                    shell_exec($command);
                }
            } else if($doAction == 'update') {
                file_put_contents('test.txt', "\r update yes", FILE_APPEND);
                $taskList = str_replace($OldCommand, $command, $taskList);
                $result = shell_exec('echo "'.trim($taskList,'').'" | crontab -');
                if($result === null){
                    return true;
                } else {
                    return false;
                }
            } else if($doAction == 'delete') {
                file_put_contents('test.txt', "\r delete yes", FILE_APPEND);
                file_put_contents('test.txt', "\r old = ".$taskList, FILE_APPEND);
                $taskList = str_replace($command, '', $taskList);
                file_put_contents('test.txt', "\r new = ".$taskList, FILE_APPEND);
                $result = shell_exec('echo "'.trim($taskList,'').'" | crontab -');
                if($result === null || $result == ''){
                    return true;
                } else {
                    return false;
                }
            } else {
                file_put_contents('test.txt', "\r type error", FILE_APPEND);
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
            $taskList = str_replace($command, '', $taskList);
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
