<?php

namespace Modules\Admin\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
use App\Services\RabbitmqService;
use Modules\Site\Http\Models\User;

class TestController extends CrudController
{
    public function TestPush(Request $request) {
        $id = $request->id;
        $data['tenant_id'] = $id;
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data = json_encode(['class' => 'Modules\Admin\Http\Controllers\TestController', 'method' => 'TestPop', 'data'=>$data]);
        RabbitmqService::push('test','test','test','fanout' ,$data);
    }

    public function TestPop($params = null) {
        $params = $params['data'];
        $id = $params['tenant_id'];
        $tenant = Tenant::find($id);
        tenancy()->initialize($tenant);
        $res = User::find(1);
        $res->name = $params['name'];
        $res->email = $params['email'];
        $res->save();
    }
}
