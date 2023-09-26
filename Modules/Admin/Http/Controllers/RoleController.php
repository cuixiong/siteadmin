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
        $data['Creaters'] = User::get();
        $data['MenuTypes'] = (new Rule())->MuenList();
        $data['Operation'] = Role::get();
        $data['Pids'] = (new Rule())->get();
        $data['RoutesVue'] = (new Rule())->select(['vue_route as name','vue_route'])->get();
        $data['States'] = (new Rule())->StatusList();
        $data['Updaters'] = User::get();
        ReturnJson(TRUE,'请求成功',$data);
    }
}
