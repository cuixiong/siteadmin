<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Role;

class RoleController extends CrudController
{
    /**
     * 返回某个角色已拥有的Admin模块权限Ids
     */
    public function adminId(Request $request)
    {
        $id = $request->id;
        if(empty($id)){
            ReturnJson(false,'id is empty');
        }
        $rule_ids = Role::where('id',$id)->value('rule_id');
        // 使用array_map()和intval()将数组中的值转换为整数
        $rule_ids = array_map('intval',$rule_ids);
        ReturnJson(TRUE,'请求成功',$rule_ids);
    }
}
