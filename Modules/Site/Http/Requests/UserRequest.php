<?php
namespace Modules\Site\Http\Requests;
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
            'user_name' => 'required|unique:users',
            'email' => 'required',
            'phone' => 'required',
            'coutry' => 'required',
            'status' => 'required',
            'company' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'user_name.required' => '登陆名不能为空',
            'user_name.unique' => '登陆名不能重复',
            'email.required' => '邮箱不能为空',
            'phone.required' => '手机号不能为空',
            'coutry.required' => '国家不能为空',
            'status.required' => '状态不能为空',
            'company.required' => '公司不能为空',
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
            'user_name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'coutry' => 'required',
            'status' => 'required',
            'company' => 'required',
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
            'user_name.required' => '登陆名不能为空',
            'user_name.unique' => '登陆名不能重复',
            'email.required' => '邮箱不能为空',
            'phone.required' => '手机号不能为空',
            'coutry.required' => '国家不能为空',
            'status.required' => '状态不能为空',
            'company.required' => '公司不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
