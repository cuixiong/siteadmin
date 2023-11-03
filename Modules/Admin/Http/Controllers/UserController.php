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
            $input['department_id'] = $input['deptId'];
            $input['role_id'] = $input['roleIds'];
            unset($input['deptId'],$input['roleIds']);
            $record = $this->ModelInstance()->create($input);
            if(!$record){
                ReturnJson(FALSE,trans('lang.add_error'));
            }
            ReturnJson(TRUE,trans('lang.add_success'),['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * AJax单个更新
     * @param $request 请求信息
     */
    protected function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $input['updated_by'] = $request->user->id;
            $input['department_id'] = $input['deptId'];
            $input['role_id'] = $input['roleIds'];
            $record = $this->ModelInstance()->findOrFail($request->id);
            if(!$record->update($input)){
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * update user info
     * @param $request 
     */
    public function updateInfo(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->user->id);
            if(!$record->update($input)){
                ReturnJson(FALSE,trans('lang.update_error'));
                exit;
            }
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
