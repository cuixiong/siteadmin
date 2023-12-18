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
    public $ErrorLog;
    public $TaskPath = '/www/server/cron/';
    public function __construct()
    {
        parent::__construct();
        $this->ErrorLog = storage_path('logs').'/'.'Timed_Taks_Error.log';
    }
    public function list(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            // 总数量
            $total = $model->count();
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $model = $model->where('parent_id',0);
            $record = $model->get();

            $data = [
                'total' => $total,
                'list' => $record
            ];
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
            ReturnJson(FALSE, $e->getMessage());
        }
    }
    public function store(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            // 随机生成任务ID
            $input['task_id'] = $task_id = $this->generateRandomString();
            $input['log_path'] = $log_path = $this->TaskPath.$task_id.'.log 2>&1';
            $input['do_command'] = $do_command = $this->CreateCommand($input['type'],$input['do_command']);
            if($do_command == false){
                ReturnJson(FALSE, trans('lang.add_error'));
            }
            $input['command'] = $this->MakeCommand($task_id,$log_path,$input['time_type'],$input['day'],$input['hour'],$input['minute'],$input['week_day']);
            $input['body'] = $this->MakeBody($do_command);
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
                            $childTaskId = $this->generateRandomString();
                            $childDoCommand = $this->MakeApiCommand($do_command,$site->api_path,$site->domain);
                            $childLogPath = $this->TaskPath.$childTaskId.'.log 2>&1';
                            $data = [
                                'task_id' => $childTaskId,
                                'site_id' => $value,
                                'do_command' => $childDoCommand,
                                'body' => $this->MakeBody($childDoCommand),
                                'log_path' => $childLogPath,
                                'command' => $this->MakeCommand($childTaskId,$childLogPath,$input['time_type'],$input['day'],$input['hour'],$input['minute'],$input['week_day']),
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
            file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            // 随机生成任务ID
            $task_id = $record->task_id;
            $input['log_path'] = $log_path = $this->TaskPath.$task_id.'.log 2>&1';
            $input['do_command'] = $do_command = $this->CreateCommand($input['type'],$input['do_command']);
            $input['command'] = $this->MakeCommand($task_id,$log_path,$input['time_type'],$input['day'],$input['hour'],$input['minute'],$input['week_day']);
            $input['body'] = $this->MakeBody($do_command);
            $input['old_command'] = $record->command;
            // 更新父任务
            if (!$record->update($input)) {   
                DB::rollback();                                                                                                                                          
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
                $childrenUpdateIds = array_intersect($childrenSiteIds,$siteIds);
                $childrenInsertIds = array_diff($siteIds,$childrenSiteIds,);
                $childrenDeleteIds = array_diff($childrenSiteIds,$siteIds);
                $updateIds = [];
                $insertIds = [];
                $deleteIds = [];
                
                // 子任务编辑（对应站点的定时任务）
                if(!empty($childrenUpdateIds)){
                    $childrenUpdateData = [];
                    foreach ($childrenUpdateIds as $key => $id) {
                        $site = Site::select(['api_path','domain'])->find($id);
                        $updateIds[] = $childrenTasks[$id]['id'];
                        $childTaskId = $childrenTasks[$id]['task_id'];
                        $childDoCommand = $this->MakeApiCommand($do_command,$site->api_path,$site->domain);
                        $childLogPath = $this->TaskPath.$childTaskId.'.log 2>&1';
                        $childrenUpdateData[] = array_merge([
                            'id' => $childrenTasks[$id]['id'],
                            'site_id' => $id,
                            'old_command' => $childrenTasks[$id]['command'],
                            'do_command' => $childDoCommand,
                            'body' => $this->MakeBody($childDoCommand),
                            'log_path' => $childLogPath,
                            'command' => $this->MakeCommand($childTaskId,$childLogPath,$input['time_type'],$input['day'],$input['hour'],$input['minute'],$input['week_day']),
                        ],$data);
                    }
                    $this->ModelInstance()->upsert($childrenUpdateData,['id'],array_keys($childrenUpdateData[0]));
                }
                // 子任务新增（对应站点的定时任务）
                if(!empty($childrenInsertIds)){
                    $model = $this->ModelInstance();
                    foreach ($childrenInsertIds as $key => $id) {
                        $site = Site::select(['api_path','domain'])->find($id);
                        $childTaskId = $this->generateRandomString();
                        $childDoCommand = $this->MakeApiCommand($do_command,$site->api_path,$site->domain);
                        $childLogPath = $this->TaskPath.$childTaskId.'.log 2>&1';
                        $InsertData = array_merge([
                            'task_id' => $childTaskId,
                            'site_id' => $id,
                            'old_command' => "",
                            'do_command' => $childDoCommand,
                            'body' => $this->MakeBody($childDoCommand),
                            'log_path' => $childLogPath,
                            'command' => $this->MakeCommand($childTaskId,$childLogPath,$input['time_type'],$input['day'],$input['hour'],$input['minute'],$input['week_day']),
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
                    $this->TimedTaskQueue($updateIds,'update');
                }
                if($insertIds){
                    $this->TimedTaskQueue($insertIds,'add');
                }
                if($deleteIds){
                    $this->TimedTaskQueue($deleteIds,'delete');
                }
            } else {
                DB::commit();
                $this->TimedTaskQueue([$record->id],'update');
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
            DB::rollback();
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
            $ids = array_merge($ids, $childrenIds);
            $res = $this->TimedTaskQueue($ids,'delete');
            if($res === true){
                ReturnJson(TRUE, trans('lang.delete_success'));
            } else {
                ReturnJson(FALSE,trans('lang.delete_error'));
            }
        } catch (\Exception $e) {
            file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    public function ExecuteTask(Request $request)
    {
        try {
            $id = $request->id;
            $task = $this->ModelInstance()->find($id);
            if(empty($task)){
                ReturnJson(FALSE, trans('lang.task_is_undefined'));
            }
            $ids = [];
            if($task->category == 'index' && $task->parent_id == '0'){
                $childrenTaskIds = $this->ModelInstance()->where('parent_id',$id)->where('status',1)->pluck('id')->toArray();
                $ids = array_merge($ids,$childrenTaskIds);
            } else {
                $ids[] = $task->id;
            }
            $res = $this->TimedTaskQueue($ids,'do');
            $res ? ReturnJson(TRUE, trans('lang.request_success')) : ReturnJson(FALSE, trans('lang.request_error'));
        } catch (\Exception $e) {
            file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    public function changeStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->id;
            $status = $request->status;
            if(!in_array($status,[0,1])){
                DB::rollBack();
                ReturnJson(false,trans('lang.request_error'));
            }
            $task = $this->ModelInstance()->find($id);
            $task->status = $status;
            $task->save();
            $ids = [];
            if($task->category == 'index' && $task->parent_id == '0'){
                $childrenTaskIds = $this->ModelInstance()->where('parent_id',$id)->pluck('id')->toArray();
                $ids = array_merge($ids,$childrenTaskIds);
                $this->ModelInstance()->where('parent_id',$id)->update(['status' => $status]);
            } else {
                $ids[] = $id;
            }
            $action = $status == 0 ? 'stop' : 'add';
            DB::commit();
            $res = $this->TimedTaskQueue($ids,$action);
            $res ? ReturnJson(true,trans('lang.request_success')) : ReturnJson(true,trans('lang.request_error'));
        } catch (\Exception $e) {
            DB::rollBack();
            file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
            ReturnJson(false,$e->getMessage());
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
            file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
            return false;
        }
    }

    public function MakeCommand($task_id,$log_path,$time_type,$day,$hour,$minute,$week_day){
        $CronTime = $this->CrateLiunxTime($time_type,$day,$hour,$minute,$week_day);
        if($CronTime == false){
            ReturnJson(FALSE, trans('lang.add_error'));
        }
        $command = $this->TaskPath.$task_id. " >> " . $log_path;
        $command = $CronTime.'  '.$command;// 组合命令
        return $command;
    }

    public function CreateCommand($type, $content)
    {
        // 根据类型进行生成liunx命令
        if($type == 'shell'){
            return ' '.$content;
        } else if($type == 'http'){
            return ' curl '.$content;
        } else {
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

    public function MakeApiCommand($command,$path,$domain)
    {
        $command = str_replace('{$path}',$path,$command);
        $command = str_replace('{$domain}',$domain,$command);
        return $command;
    }

    public function DoTimedTask($params = null)
    {
        try {
            if($params){
                $params = $params['data'];
                $task = TimedTask::find($params['id']);
                if($task->category == 'admin' || ($task->category == 'index' && $task->parent_id != '0')){
                    file_put_contents($this->ErrorLog,"\r".$params['action'],FILE_APPEND);
                    $res = $this->LiunxTimedTask($params['action'],$task);
                }
                // if($task->category == 'admin'){
                //     $command = $params['action'] == 'do' ? $this->CreateCommand($task->type,$task->do_command) : $task->command;
                //     $this->LocalHostTask($params['action'],$command,$task->old_command);
                // } else if($task->category == 'index' && $task->parent_id != '0') {
                //     $serverId = Site::where('id',$task->site_id)->value('server_id');
                //     $server = Server::where('id',$serverId)->first();
                //     $site = Site::select(['api_path','domain'])->where('id',$task->site_id)->first();
                //     $command = $params['action'] == 'do' ? $this->MakeApiCommand($this->CreateCommand($task->type,$task->do_command),$site->api_path,$site->domain) : $task->command;
                //     $this->ShhTask($server->ip,$server->username,$server->password,$params['action'],$command,$task->old_command);
                // }
                if($params['action'] == 'delete'){
                    // 先删除子任务
                    TimedTask::where('parent_id',$params['id'])->delete();
                    // 删除自身任务
                    TimedTask::where('id',$params['id'])->delete();
                }
                return true;
            }
        } catch (\Exception $e) {
            file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
            return false;
        }
    }

    // public function LocalHostTask($doAction,$command,$OldCommand = '')
    // {
    //     try {
    //         $CrontabList = shell_exec('crontab -l');
    //         $CrontabList = trim($CrontabList,'');

    //         $CrontabList = array_filter(explode('\n',$CrontabList));

    //         $CrontabList = array_map(function($v){
    //             $v = trim($v,' ');
    //             $v = trim($v,"\n");
    //             return $v;
    //         },$CrontabList);

    //         switch ($doAction) {
    //             case 'add':
    //                 if (!in_array($command, $CrontabList)){
    //                     $CrontabList = implode("\n",$CrontabList);
    //                     $command = 'echo "'.$CrontabList.PHP_EOL.trim($command,'').'" | crontab -';
    //                 }
    //             break;
    //             case 'update':
    //                 $CrontabList = implode("\n",$CrontabList);
    //                 $command = str_replace($OldCommand, $command, $CrontabList);
    //                 $command = 'echo "'.trim($command,'').'" | crontab -';
    //             break;
    //             case 'delete':
    //             case 'stop':
    //                 $CrontabList = implode("\n",$CrontabList);
    //                 $command = str_replace($command, '', $CrontabList);
    //                 $command = 'echo "'.trim($command,'').'" | crontab -';
    //             break;
    //             case 'do':
    //             break;
                
    //             default:
    //                 return false;
    //             break;
    //         }
    //         $result = shell_exec($command);
    //         return true;
    //     } catch (\Exception $e) {
    //         file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
    //         return false;
    //     }
    // }

    // public function ShhTask($ip,$username,$password,$doAction,$command,$OldCommand = '')
    // {
    //     try {
    //         $ssh = new SSH2($ip);
    //         $res = $ssh->login($username,$password);
    //         if(!$res){
    //             return false;
    //         }
    //         $CrontabList = $ssh->exec('crontab -l');
    //         $CrontabList = trim($CrontabList,'');

    //         $CrontabList = array_filter(explode('\n',$CrontabList));

    //         $CrontabList = array_map(function($v){
    //             $v = trim($v,' ');
    //             $v = trim($v,"\n");
    //             return $v;
    //         },$CrontabList);

    //         switch ($doAction) {
    //             case 'add':
    //                 if (!in_array($command, $CrontabList)){
    //                     $CrontabList = implode("\n",$CrontabList);
    //                     $command = 'echo "'.$CrontabList.PHP_EOL.trim($command,'').'" | crontab -';
    //                 }
    //             break;
    //             case 'update':
    //                 $CrontabList = implode("\n",$CrontabList);
    //                 $command = str_replace($OldCommand, $command, $CrontabList);
    //                 $command = 'echo "'.trim($command,'').'" | crontab -';
    //             break;
    //             case 'delete':
    //             case 'stop':
    //                 $CrontabList = implode("\n",$CrontabList);
    //                 $command = str_replace($command, '', $CrontabList);
    //                 $command = 'echo "'.trim($command,'').'" | crontab -';
    //             break;
    //             case 'do':
    //             break;
                
    //             default:
    //                 return false;
    //             break;
    //         }
    //         $result = $ssh->exec($command);
    //         return true;
    //     } catch (\Exception $e) {
    //         file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
    //         return false;
    //     }
    // }

    public function LiunxTimedTask($doAction,$task)
    {
        try {
            if($task->category == 'index'){
                $serverId = Site::where('id',$task->site_id)->value('server_id');
                $server = Server::where('id',$serverId)->first();
                $ssh = new SSH2($server->ip);
                $res = $ssh->login($server->username,$server->password);
                if(!$res){
                    return false;
                }
            }

            $CrontabList = shell_exec('crontab -l');
            $CrontabList = trim($CrontabList,'');

            $CrontabList = array_filter(explode('\n',$CrontabList));

            $CrontabList = array_map(function($v){
                $v = trim($v,' ');
                $v = trim($v,"\n");
                return $v;
            },$CrontabList);
            file_put_contents($this->ErrorLog,"\r".json_encode($CrontabList),FILE_APPEND);
            switch ($doAction) {
                case 'add':
                    if (!in_array($task->command, $CrontabList)){
                        $CrontabList = implode("\n",$CrontabList);
                        $command = 'echo "'.$CrontabList.PHP_EOL.trim($task->command,'').'" | crontab -';
                        $FileCommand = 'echo -e "'.$task->body.'" >> '.$this->TaskPath.$task->task_id;
                        shell_exec($FileCommand);
                        // 设置文件权限
                        file_put_contents($this->ErrorLog,"\r"."chmod 700 ".$this->TaskPath.$task->task_id,FILE_APPEND);
                        shell_exec("chmod 700 ".$this->TaskPath.$task->task_id);
                    }
                break;
                case 'update':
                    $CrontabList = implode("\n",$CrontabList);
                    $command = str_replace($task->OldCommand, $task->command, $CrontabList);
                    $command = 'echo "'.trim($command,'').'" | crontab -';
                    shell_exec("cat /dev/null > ".$this->TaskPath.$task->task_id);
                    $FileCommand = 'echo -e "'.$task->body.'" >> '.$this->TaskPath.$task->task_id;
                    shell_exec($FileCommand);
                break;
                case 'delete':
                case 'stop':
                    $CrontabList = implode("\n",$CrontabList);
                    $command = str_replace($task->command, '', $CrontabList);
                    $command = 'echo "'.trim($command,'').'" | crontab -';
                    $FileCommand = 'rm '.$this->TaskPath.$task->task_id;
                    shell_exec($FileCommand);
                break;
                case 'do':
                break;
                    $command = $task->do_command;
                default:
                    return false;
                break;
            }
            file_put_contents($this->ErrorLog,"\r".$command,FILE_APPEND);
            $result = shell_exec($command);
            file_put_contents($this->ErrorLog,"\r res=".$result,FILE_APPEND);
            return true;
        } catch (\Exception $e) {
            file_put_contents($this->ErrorLog,"\r".$e->getMessage(),FILE_APPEND);
            return false;
        }
    }

    public function Logs(Request $request){
        $id = $request->id;
        $task = $this->ModelInstance()->find($id);
        $command = "cat $task->log_path";
        if($task->category == 'index'){
            $serverId = Site::where('id',$task->site_id)->value('server_id');
            $server = Server::where('id',$serverId)->first();
            $ssh = new SSH2($server->ip);
            $res = $ssh->login($server->username,$server->password);
            if(!$res){
                ReturnJson(false,'');
            }
        }
        $content = shell_exec($command);
        ReturnJson(true,'',$content);
    }

    private function generateRandomString() {
        $randomString = $this->MakeRandomString();
        return $randomString;
    }

    private function MakeRandomString()
    {
        $randomString = md5(time());
        $count = $this->ModelInstance()->where('task_id',$randomString)->where('parent_id',0)->count();
        if($count > 0){
            $randomString = $this->MakeRandomString();
        }
        return $randomString;
    }

    private function MakeBody($command)
    {
        $body = '
                #!/bin/bash
                PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
                export PATH
                '.$command.'
                echo "----------------------------------------------------------------------------"
                endDate=`date +"%Y-%m-%d %H:%M:%S"`
                echo "★[$endDate] Successful"
                echo "----------------------------------------------------------------------------"
            ';
            $body = str_replace('    ','',$body);
        return $body;
    }
}