<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;

class UsersExport implements FromArray
{
    /**
    * @return \Illuminate\Support\Array
    */
    public function array(): array
    {
        $title = ['名称','昵称','密码','邮箱','状态','手机号','部门','性别','登陆时间'];
        $list = User::select('name','nickname','password','email','role_id','status','mobile','department_id','gender','login_at')->get()->toArray();
        array_unshift($list,$title);
        return $list;
    }
}
