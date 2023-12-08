<?php

namespace Modules\Admin\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
use App\Services\RabbitmqService;
use FFI;
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

    public function task(){
        // 每一分钟执行  */1 * * * *
        // 设置定时任务
        exec('*/1 * * * * curl http://yadmin.qyrdata.com/api/admin/test/my',$res);
        var_dump($res);die;
    }
}
