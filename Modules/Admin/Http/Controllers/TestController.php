<?php

namespace Modules\Admin\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
use App\Services\RabbitmqService;
use FFI;
use Modules\Admin\Http\Models\TimedTask;
use Modules\Site\Http\Models\User;

class TestController extends CrudController
{
    public function TestPush(Request $request) {
        $id = $request->id;
        $data['tenant_id'] = $id;
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data = json_encode(['class' => 'Modules\Admin\Http\Controllers\TestController', 'method' => 'TestPop', 'data'=>$data]);
        $RabbitMQ = new RabbitmqService();
        $RabbitMQ->setQueueName('test');// 设置队列名称
        $RabbitMQ->setExchangeName('test');// 设置交换机名称
        $RabbitMQ->setQueueMode('fanout');// 设置队列模式
        $RabbitMQ->push($data);// 推送数据
        echo '推送成功';
    }

    public function TestPop($params = null) {
        file_put_contents('a.txt',json_encode($params)."\r\n",FILE_APPEND);
        // var_dump(123);die;
    }

    public function Test01(Request $request) {
        $id = $request->id;
        $data['tenant_id'] = $id;
        $data['name'] = 'queue_1';
        $data['email'] = 'queue_1';
        $data = json_encode(['class' => 'Modules\Admin\Http\Controllers\TestController', 'method' => 'TestPop01', 'data'=>$data]);
        RabbitmqService::push('test_queue01','test','test','fanout' ,$data);
    }
    
    public function my(){
        file_put_contents('123.txt',date('Y-m-d H:i:s',time()),FILE_APPEND);
    }

    public function task(Request $request){
        // $content = shell_exec('crontab -l');
        // var_dump($content);die;
        
        if($request->id){
            $content = shell_exec('crontab -l');
            // if(!empty($content)){
            //     file_put_contents('123.txt',"\r".$content,FILE_APPEND);
            // }
            // var_dump($content);die;
            // $res = (new TimedTaskController)->LocalHostTask('add','*/1 * * * * curl http://yadmin.qyrdata.com/api/admin/test/task >> /var/www/html/logs/170236679063905.log 2>&1');
            // $command = 'echo "*/1 * * * * curl http://yadmin.qyrdata.com/api/admin/test/task  >> /www/wwwroot/yadmin/admin/Modules/Admin/170236679063905.log 2>&1" | crontab -';
            $command = 'echo "*/1 * * * * curl http://yadmin.qyrdata.com/api/admin/test/task  >> /www/wwwroot/yadmin/admin/Modules/Admin/170236679063905.log 2>&1" | crontab -';
            // $content = str_replace("*/1 * * * * curl http://yadmin.qyrdata.com/api/admin/test/task  >> /www/wwwroot/yadmin/admin/Modules/Admin/170236679063905.log 2>&1",'',$content);
            // // var_dump($content);die;
            // $command = 'echo "'.$content.'" | crontab -';
            // $taskList = shell_exec('crontab -l');
            // $taskListArr = array_filter(explode('\n',$taskList));
            // var_dump($taskListArr);
            // if (in_array($command, $taskListArr)){
            //     shell_exec($command);
            // }
            shell_exec($command);
            $content = shell_exec('crontab -l');
            var_dump($content);
        } else {
            file_put_contents('123.txt',"\r".date('Y-m-d H:i:s',time()),FILE_APPEND);
        }

    }
    public function task1(Request $request){
        file_put_contents('1.txt',"\r".date('Y-m-d H:i:s',time()),FILE_APPEND);
    }
    public function task2(Request $request){
        file_put_contents('2.txt',"\r".date('Y-m-d H:i:s',time()),FILE_APPEND);
    }
    public function task3(Request $request){
        file_put_contents('3.txt',"\r".date('Y-m-d H:i:s',time()),FILE_APPEND);
    }
    public function task4(Request $request){
        file_put_contents('4.txt',"\r".date('Y-m-d H:i:s',time()),FILE_APPEND);
    }
    public function task5(Request $request){
        file_put_contents('5.txt',"\r".date('Y-m-d H:i:s',time()),FILE_APPEND);
    }
    public function task6(Request $request){
        file_put_contents('6.txt',"\r".date('Y-m-d H:i:s',time()),FILE_APPEND);
    }
}
