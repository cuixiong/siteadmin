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
        if(!empty($rule_ids)){
            // 使用array_map()和intval()将数组中的值转换为整数
            $rule_ids = array_map('intval',$rule_ids);
        } else {
            $rule_ids = [];
        }
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

    /**
     * Admin模块新增权限
     */
    public function adminRule(Request $request) {
        try {
            if(empty($request->roleId)){
                ReturnJson(FALSE,'roleId is empty');
            }
            if(empty($request->id)){
                ReturnJson(FALSE,'id is empty');
            }
            $input = $request->all();
            $input['rule_id'] = $input['roleId'];
            unset($input['roleId']);
            $input['updated_by'] = $request->user->id;
            $record = $this->ModelInstance()->findOrFail($request->id);
            if(!$record->update($input)){
                ReturnJson(FALSE,'更新失败');
            }
            ReturnJson(TRUE,'更新成功');
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
