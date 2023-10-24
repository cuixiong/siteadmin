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
            'rule_id' => 'required',
            'site_rule_id' => 'required',
            'data_scope' => 'required',
            'code' => 'required',
            'is_super_administrator' => 'required',
        ];
        $message = [
            'name.required' => '角色名称不能为空',
            'status.required' => '状态不能为空',
            'rule_id.required' => '权限不能为空',
            'site_rule_id.required' => '站点权限不能为空',
            'data_scope.required' => '数据权限不能为空',
            'code.required' => '编码不能为空',
            'is_super_administrator.required' => '管理员状态为空',
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
            'rule_id' => 'required',
            'site_rule_id' => 'required',
            'data_scope' => 'required',
            'code' => 'required',
            'is_super_administrator' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '角色名称不能为空',
            'status.required' => '状态不能为空',
            'rule_id.required' => '权限不能为空',
            'site_rule_id.required' => '站点权限不能为空',
            'data_scope.required' => '数据权限不能为空',
            'code.required' => '编码不能为空',
            'is_super_administrator.required' => '管理员状态为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
