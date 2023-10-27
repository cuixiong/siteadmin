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
            'name' => 'required',
            'status' => 'required',
            'data_scope' => 'required',
            'code' => 'required|unique:roles',
            'sort' => 'required',
            'is_super' => 'required', 
        ];
        $message = [
            'code.unique' => '编码已存在，请更换其他编码',
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
        return $this->validateRequest($request, $rules);
    }
}
