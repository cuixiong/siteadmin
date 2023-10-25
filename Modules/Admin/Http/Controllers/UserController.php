<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;

class UserController extends CrudController
{
    /**
     * 单个新增
     * @param $request 请求信息
     */
    protected function store(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $input['created_by'] = $request->user->id;
            // 为什么写这下面的代码，问前端（要求必须这么传过来，谁家数据库字段命名用大写命名？）
            $input['department_id'] = $input['deptId'];
            $input['role_id'] = $input['roleIds'];
            unset($input['deptId'],$input['roleIds']);
            $record = $this->ModelInstance()->create($input);
            if(!$record){
                ReturnJson(FALSE,'新增失败');
            }
            ReturnJson(TRUE,'新增成功',['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
