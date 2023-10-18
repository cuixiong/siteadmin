<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Admin\Http\Models\Email;
use Modules\Site\Http\Models\User;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function select(Request $request)
    {
        $model = new User();
        $users = $model->get()->toArray();
        $res = Email::get()->toArray();
        var_dump('站点用户列表',$users,'==========================');
        var_dump('总控邮箱列表',$users,'==========================');
        die;
    }

    public function update(Request $request)
    {
        $user = User::find(1);
        $user->name = '测试时间戳'.time();
        $user->save();
        var_dump('更新成功');die;
    }

    public function insert(Request $request)
    {
        $user = new User();
        $user->name = '测试时间戳'.time();
        $user->email = time().'@qq.com';
        $user->email_verified_at = date('Y-m-d H:i:s',time());
        $user->password = '123';
        $user->remember_token = '123';
        $user->created_at = date('Y-m-d H:i:s',time());
        $user->updated_at = date('Y-m-d H:i:s',time());
        $user->save();
        var_dump('新增成功');die;
    }

    public function delete(Request $request)
    {
        $user = User::find($request->id);
        $user->delete();
        var_dump('删除成功');die;
    }
}
