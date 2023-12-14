<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class RoleRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required|unique:roles',
            'status' => 'required',
            'data_scope' => 'required',
            'code' => 'required|unique:roles',
            'sort' => 'required',
            'is_super' => 'required', 
        ];
        $message = [
            'name.required' => trans('lang.role_name_empty'),
            'name.unique' => trans('lang.role_name_exists'),
            'status.required' => trans('lang.status_empty'),
            'data_scope.required' => trans('lang.data_scope_empty'),
            'sort.required' => trans('lang.sort_empty'),
            'is_super.required' => trans('lang.is_super_empty'),
            'code.required' => trans('lang.code_empty'),
            'code.unique' => trans('lang.code_no_pass'),
        ];
        return $this->validateRequest($request, $rules,$message);
    }

    /**
     * 更新数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function update($request)
    {
        $rules = [
            'id' => 'required',
            'name' => 'required',
            'status' => 'required',
            'data_scope' => 'required',
            'code' => 'required',
            'sort' => 'required',
            'is_super' => 'required',
        ];
        $message = [
            'id.required' => trans('lang.id_empty'),
            'name.required' => trans('lang.role_name_empty'),
            'status.required' => trans('lang.status_empty'),
            'data_scope.required' => trans('lang.data_scope_empty'),
            'sort.required' => trans('lang.sort_empty'),
            'is_super.required' => trans('lang.is_super_empty'),
            'code.required' => trans('lang.code_empty'),
            'code.unique' => trans('lang.code_no_pass'),
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
