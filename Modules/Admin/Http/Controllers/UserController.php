<?php

namespace Modules\Admin\Http\Controllers;

use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\Position;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\User;

class UserController extends CrudController
{
    /**
     * 用户管理列表帅选器
     */
    public function filters()
    {
        $data = [];
        $data['Positions'] = Position::get();
        $data['Roles'] = Role::get();
        $data['Roles'] = Role::get();
        $data['isOnJob'] = (new User())->IsOnJobList();
        $data['States'] = (new User())->StatusList();
        ReturnJson(TRUE,'请求成功',$data);
    }
}
