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
            'nickname' => 'required',
            'email' => 'required|unique:users',
            'position_id' => 'required',
            'roleIds' => 'required',
            'gender' => 'required',
            'deptId' => 'required',
        ];
        $message = [
            'username.required' => '用户名不能为空',
            'nickname.required' => '用户昵称不能为空',
            'email.required' => '邮箱不能为空',
            'email.unique' => '邮箱已存在，请更换其他邮箱',
            'position_id.required' => '职位ID不能为空',
            'roleIds.required' => '角色ID不能为空',
            'gender.required' => '性别不能为空',
            'deptId.required' => '部门ID不能为空',

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
            'nickname' => 'required',
            'email' => 'required',
            'position_id' => 'required',
            'roleIds' => 'required',
            'gender' => 'required',
            'deptId' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'username.required' => '用户名不能为空',
            'nickname.required' => '用户昵称不能为空',
            'email.required' => '邮箱不能为空',
            'position_id.required' => '职位ID不能为空',
            'roleIds.required' => '角色ID不能为空',
            'gender.required' => '性别不能为空',
            'deptId.required' => '部门ID不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
