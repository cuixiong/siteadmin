<?php

namespace Modules\Admin\Http\Controllers;

use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\User;
use Modules\Admin\Http\Models\Rule;

class RoleController extends CrudController
{
    /**
     * 权限管理列表筛选器
     */
    public function filters()
    {
        $data = [];
        $data['Creaters'] = User::get()->toArray();
        array_push($data['Creaters'],["id" =>'','name'=>'创建者']);
        $data['States'] = (new Rule())->StatusList();
        $data['Updaters'] = User::get()->toArray();;
        array_push($data['Updaters'],["id" =>'','name'=>'更新者']);
        ReturnJson(TRUE,'请求成功',$data);
    }
}
