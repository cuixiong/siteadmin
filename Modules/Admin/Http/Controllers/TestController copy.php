<?php

namespace Modules\Admin\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
use Modules\Site\Http\Models\User;

class TestController extends CrudController
{
    public function TestPush(Request $request) {
        $id = $request->id;
        $data['tenant_id'] = $id;
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data = json_encode(['class' => 'Modules\Admin\Http\Controllers\TestController', 'method' => 'TestPop', 'data'=>$data]);
//        RabbitmqService::push('test_queue01','test_qq','test','fanout' ,$data);
    }

    public function TestPop($params = null) {
        // file_put_contents('a.txt',json_encode($params),FILE_APPEND);
        var_dump(123);die;
    }

    public function Test01(Request $request) {
        $id = $request->id;
        $data['tenant_id'] = $id;
        $data['name'] = 'queue_1';
        $data['email'] = 'queue_1';
        $data = json_encode(['class' => 'Modules\Admin\Http\Controllers\TestController', 'method' => 'TestPop01', 'data'=>$data]);
//        RabbitmqService::push('test_queue01','test','test','fanout' ,$data);
    }

}
