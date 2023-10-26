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
        $rule_ids = empty($rule_ids) ? [] : $rule_ids;
        // 使用array_map()和intval()将数组中的值转换为整数
        $rule_ids = array_map('intval',$rule_ids);
        ReturnJson(TRUE,'请求成功',$rule_ids);
    }

    /**
     * 递归树状value-label格式
     */
    public function option () {
        try {
            $list = (new Role)->GetList(['id','id as value','name as label']);
            ReturnJson(TRUE,'请求成功',$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
