<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class EmailRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required',
            'host' => 'required',
            'port' => 'required',
            'encryption' => 'required',
            'password' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'email.required' => '邮箱不能为空',
            'host.required' => 'SMTP主机地址不能为空',
            'port.required' => 'SMTP主机端口不能为空',
            'encryption.required' => 'SMTP加密类型不能为空',
            'password.required' => '邮箱授权码不能为空',
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
            'email' => 'required',
            'host' => 'required',
            'port' => 'required',
            'encryption' => 'required',
            'password' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
            'email.required' => '邮箱不能为空',
            'host.required' => 'SMTP主机地址不能为空',
            'port.required' => 'SMTP主机端口不能为空',
            'encryption.required' => 'SMTP加密类型不能为空',
            'password.required' => '邮箱授权码不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
