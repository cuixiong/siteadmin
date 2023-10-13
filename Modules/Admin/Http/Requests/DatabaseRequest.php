<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class DatabaseRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'ip' => 'required',
            'name' => 'required',
            'username' => 'required',
            'password' => 'required',
        ];
        $message = [
            'ip.required' => 'IP不能为空',
            'name.required' => '名称不能为空',
            'username.required' => '用户名不能为空',
            'password.required' => '密码不能为空',
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
            'ip' => 'required',
            'name' => 'required',
            'username' => 'required',
            'password' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'ip.required' => 'IP不能为空',
            'name.required' => '名称不能为空',
            'username.required' => '用户名不能为空',
            'password.required' => '密码不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
