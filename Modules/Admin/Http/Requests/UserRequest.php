<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class UserRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|unique:users',
            'position_id' => 'required',
            'role_id' => 'required',
        ];
        $meassge = [
            'name.required' => '用户名不能为空',
            'email.required' => '邮箱不能为空',
            'email.unique' => '邮箱已存在，请更换其他邮箱',
            'position_id.required' => '职位ID不能为空',
            'role_id.required' => '角色ID不能为空',
        ];
        return $this->validateRequest($request, $rules,$meassge);
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
            'email' => 'required|unique:users',
            'position_id' => 'required',
            'role_id' => 'required',
        ];
        $meassge = [
            'id.required' => '用户名不能为空',
            'name.required' => '用户名不能为空',
            'email.required' => '邮箱不能为空',
            'email.unique' => '邮箱已存在，请更换其他邮箱',
            'position_id.required' => '职位ID不能为空',
            'role_id.required' => '角色ID不能为空',
        ];
        return $this->validateRequest($request, $rules,$meassge);
    }
}
