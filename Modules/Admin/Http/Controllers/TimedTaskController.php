<?php

namespace Modules\Admin\Http\Controllers;

use App\Services\RabbitmqService;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\Server;
use Modules\Admin\Http\Models\Site;
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
            $log_name = time(). rand(10000, 99999).'.log 2>&1';
            $input['log_path'] = $log_path = public_path().'/'.$log_name;// 本地服务器Log
            $command = $this->MakeCommand($input['type'],$input['do_command'],$input['time_type'],$input['day'],$input['hour'],$input['minute'],$input['week_day']);
            $input['command'] = $command.$log_path;
            DB::beginTransaction();
            try {
                $record = (new TimedTask())->create($input);
                if (!$record) {
                    DB::rollBack();
                    ReturnJson(FALSE, trans('lang.add_error'));
                }
                $ids = [];
                if($input['category'] == 'admin'){
                    $ids[] = $record->id;
                } else {
                    if(!empty($input['site_id'])){
                        $CommonData = [
                            'parent_id' => $record->id,
                            'name' => $input['name'],
                            'type' => $input['type'],
                            'status' => $input['status'],
                            'day' => $input['day'],
                            'hour' => $input['hour'],
                            'minute' => $input['minute'],
                            'week_day' => $input['week_day'],
                            'category' => $input['category'],
                            'time_type' => $input['time_type'],
                            'created_by' => $request->user->id,
                            'updated_by' => $request->user->id,
                            'updated_at' => time(),
                            'created_at' => time(),
                        ];
                        $model = $this->ModelInstance();
                        foreach (explode(',', $input['site_id']) as $key => $value) {
                            $site = Site::select(['id','api_path','domain'])->find($value);
                            $ApiLogPath = rtrim($site->api_path,'/').'/'.$log_name;// 定义日志文件路径
                            $data = [
                                'site_id' => $value,
                                'do_command' => $this->MakeApiCommand($input['do_command'],$site->api_path,$site->domain),
                                'command' => $this->MakeApiCommand($command,$site->api_path,$site->domain).$ApiLogPath,
                                'log_path' => $ApiLogPath,
                            ];
                            $data = array_merge($data,$CommonData);
                            $id = $model->insertGetId($data);
                            if($id){ $ids[] = $id; }
                        }
                    }
                }
                DB::commit();
                $res = $this->TimedTaskQueue($ids,'add');
                $res ? ReturnJson(true, trans('lang.add_success')) : ReturnJson(FALSE, trans('lang.add_error'));
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
            DB::beginTransaction();
            $this->ValidateInstance($request);
            $input = $request->all();
            // 定义日志文件路径
            $log_name = time(). rand(10000, 99999).'.log 2>&1';
            $input['log_path'] = $log_path = public_path().'/'.$log_name;// 本地服务器Log
            $command = $this->MakeCommand($input['type'],$input['do_command'],$input['time_type'],$input['day'],$input['hour'],$input['minute'],$input['week_day']);
            $input['command'] = $command.$log_path;
            $record = $this->ModelInstance()->findOrFail($request->id);
            $OldCommand = $record->command;
            // 更新父任务
            if (!$record->update($input)) {                                                                                                                                             
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            // 更新子任务
            if($record->category == 'index'){
                $data = [
                    'parent_id' => $record->id,
                    'name' => $record->name,
                    'type' => $record->type,
                    'status' => $record->status,
                    'day' => $record->day,
                    'hour' => $record->hour,
                    'minute' => $record->minute,
                    'week_day' => $record->week_day,
                    'category' => $record->category,
                    'time_type' => $record->time_type,
                    'updated_by' => $request->user->id,
                    'updated_at' => time(),
                ];
                $childrenTasks = $this->ModelInstance()->where('parent_id',$record->id)->get()->toArray();
                $siteIds = $record->site_id ? explode(',', $record->site_id) : [];
                $childrenTasks = array_column($childrenTasks,null,'site_id');
                $childrenSiteIds = array_keys($childrenTasks);
                // var_dump($childrenSiteIds);die;
                $childrenUpdateIds = array_intersect($childrenSiteIds,$siteIds);
                // var_dump($childrenUpdateIds);die;
                $childrenInsertIds = array_diff($siteIds,$childrenSiteIds,);
                // var_dump($childrenInsertIds);die;
                $childrenDeleteIds = array_diff($childrenSiteIds,$siteIds);
                $updateIds = [];
                $insertIds = [];
                $deleteIds = [];
                
                // 子任务编辑（对应站点的定时任务）
                if(!empty($childrenUpdateIds)){
                    $childrenUpdateData = [];
                    foreach ($childrenUpdateIds as $key => $id) {
                        $site = Site::select(['api_path','domain'])->find($id);
                        $ApiLogPath = rtrim($site->api_path,'/').'/'.$log_name;// 定义日志文件路径
                        $updateIds[] = $childrenTasks[$id]['id'];
                        $childrenUpdateData[] = array_merge([
                            'site_id' => $id,
                            'id' => $childrenTasks[$id]['id'],
                            'do_command' => $this->MakeApiCommand($record->do_command,$site->api_path,$site->domain),
                            'command' => $this->MakeApiCommand($command,$site->api_path,$site->domain).$ApiLogPath,
                            'old_command' => $childrenTasks[$id]['command'],
                            'log_path' => $ApiLogPath,
                        ],$data);
                    }
                    $this->ModelInstance()->upsert($childrenUpdateData,['id'],array_keys($childrenUpdateData[0]));
                }
                // 子任务新增（对应站点的定时任务）
                if(!empty($childrenInsertIds)){
                    $model = $this->ModelInstance();
                    foreach ($childrenInsertIds as $key => $id) {
                        $site = Site::select(['api_path','domain'])->find($id);
                        $ApiLogPath = rtrim($site->api_path,'/').'/'.$log_name;// 定义日志文件路径
                        $InsertData = array_merge([
                            'site_id' => $id,
                            'do_command' => $this->MakeApiCommand($record->do_command,$site->api_path,$site->domain),
                            'command' => $this->MakeApiCommand($command,$site->api_path,$site->domain).$ApiLogPath,
                            'old_command' => "",
                            'log_path' => $ApiLogPath,
                        ],$data);
                        $id = $model->insertGetId($InsertData);
                        $insertIds[] = $id;
                    }
                }
                // 子任务删除（对应站点的定时任务）
                if(!empty($childrenDeleteIds)){
                    foreach ($childrenDeleteIds as $key => $id) {
                        $deleteIds[] = $childrenTasks[$id]['id'];
                    }
                }
                DB::commit();
                if($updateIds){
                    $this->TimedTaskQueue($updateIds,'update',$OldCommand);
                }
                if($insertIds){
                    $this->TimedTaskQueue($insertIds,'add');
                }
                if($deleteIds){
                    $this->TimedTaskQueue($deleteIds,'delete');
                }
            } else {
                DB::commit();
                $this->TimedTaskQueue([$record->id],'update',$OldCommand);
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
            $res = $this->TimedTaskQueue($ids,'delete');
            // $res = true;
            if($res === true){
                ReturnJson(TRUE, trans('lang.delete_success'));
            } else {
                ReturnJson(FALSE,trans('lang.delete_error'));
            }
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    public function MakeCommand($type,$doCommand,$time_type,$day,$hour,$minute,$week_day){
        $command = $this->CreateCommand($type,$doCommand);
        if($command == false){
            ReturnJson(FALSE, trans('lang.add_error'));
        }
        $CronTime = $this->CrateLiunxTime($time_type,$day,$hour,$minute,$week_day);
        if($CronTime == false){
            ReturnJson(FALSE, trans('lang.add_error'));
        }
        $command = $CronTime.$command;// 组合命令
        return $command;
    }

    public function CreateCommand($type, $content)
    {
        // 根据类型进行生成liunx命令
        if($type == 'shell'){
            return ' '.$content . "  >> ";
        } else if($type == 'http'){
            return ' curl '.$content . " >> ";
        } else {
            return false;
        }
    }

    public function TimedTaskQueue($ids,$action)
    {
        try {
            $RabbitMQ = new RabbitmqService();
            $RabbitMQ->setQueueName('timed_task');
            foreach ($ids as $id) {
                $RabbitMQ->SimpleModePush('Modules\Admin\Http\Controllers\TimedTaskController','DoTimedTask',['id' => $id, 'action' => $action]);
            }
            $RabbitMQ->close();
            return true;
        } catch (\Exception $e) {
            file_put_contents('error_log.txt', "\r".json_encode($e->getMessage()), FILE_APPEND);
            return false;
        }
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
                    file_put_contents('test.txt', "\r admin yes", FILE_APPEND);
                    $res = $this->LocalHostTask($params['action'],$task->command,$task->old_command);
                } else if($task->category == 'index') {
                    file_put_contents('test.txt', "\r index yes", FILE_APPEND);
                    $serverId = Site::where('id',$task->site_id)->value('server_id');
                    $server = Server::where('id',$serverId)->first();
                    file_put_contents('test.txt', "\r server yes".$server->ip.$server->username.$server->password, FILE_APPEND);
                    $this->ShhTask($server->ip,$server->username,$server->password,$params['action'],$task->command,$task->old_command);
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
                file_put_contents('test.txt', "\r OldCommand=".$OldCommand, FILE_APPEND);
                file_put_contents('test.txt', "\r command=".$command, FILE_APPEND);
                file_put_contents('test.txt', "\r taskList=".$taskList, FILE_APPEND);
                $taskList = str_replace($OldCommand, $command, $taskList);
                file_put_contents('test.txt', "\r NewTaskList=".$taskList, FILE_APPEND);
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
        file_put_contents('test.txt', "\r ssh ip =".$ip, FILE_APPEND);
        file_put_contents('test.txt', "\r ssh username =".$username, FILE_APPEND);
        file_put_contents('test.txt', "\r ssh password =".$password, FILE_APPEND);
        file_put_contents('test.txt', "\r ssh doAction =".$doAction, FILE_APPEND);
        file_put_contents('test.txt', "\r ssh command =".$command, FILE_APPEND);
        file_put_contents('test.txt', "\r ssh OldCommand =".$OldCommand, FILE_APPEND);
        $ssh = new SSH2($ip);
        $res = $ssh->login($username,$password);
        if(!$res){
            return false;
        }
        $taskList = $ssh->exec('crontab -l');
        file_put_contents('test.txt', "\r totalTask=".$taskList, FILE_APPEND);
        if($doAction == 'add'){
            file_put_contents('test.txt', "\r add=yes", FILE_APPEND);
            $taskListArr = array_filter(explode('\n',$taskList));
            if (!in_array($command, $taskListArr)){
                file_put_contents('test.txt', "\r add in_array=yes", FILE_APPEND);
                $command = 'echo "'.trim($command,'').'" | crontab -';
                file_put_contents('test.txt', "\r add shh command=".$command, FILE_APPEND);
                $ssh->exec($command);
            }
        } else if($doAction == 'update') {
            file_put_contents('test.txt', "\r update shh OldCommand=".$OldCommand, FILE_APPEND);
            file_put_contents('test.txt', "\r update shh command=".$command, FILE_APPEND);
            file_put_contents('test.txt', "\r update shh taskList=".$taskList, FILE_APPEND);
            $taskList = str_replace($OldCommand, $command, $taskList);
            file_put_contents('test.txt', "\r update shh NewTaskList=".$taskList, FILE_APPEND);
            $result = $ssh->exec('echo "'.trim($taskList,'').'" | crontab -');
            if($result === null){
                return true;
            } else {
                return false;
            }
        } else if($doAction == 'delete') {
            file_put_contents('test.txt', "\r delete shh command=".$command, FILE_APPEND);
            file_put_contents('test.txt', "\r delete shh taskList=".$taskList, FILE_APPEND);
            $taskList = str_replace($command, '', $taskList);
            file_put_contents('test.txt', "\r delete shh NewTaskList=".$taskList, FILE_APPEND);
            $result = $ssh->exec('echo "'.trim($taskList,'').'" | crontab -');
            file_put_contents('test.txt', "\r delete shh result=".$result, FILE_APPEND);
            if($result === null){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function MakeApiCommand($command,$path,$domain)
    {
        $command = str_replace('{$path}',$path,$command);
        $command = str_replace('{$domain}',$domain,$command);
        return $command;
    }
}
