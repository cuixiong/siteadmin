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
            'rule_id' => 'required'
        ];
        $message = [
            'name.required' => '角色名称不能为空',
            'status.required' => '状态不能为空',
            'rule_id.required' => '权限不能为空'
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
            'name' => 'required',
            'status' => 'required',
            'rule_id' => 'required'
        ];
        $message = [
            'name.required' => '角色名称不能为空',
            'status.required' => '状态不能为空',
            'rule_id.required' => '权限不能为空'
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
