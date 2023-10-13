<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class ServerRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'language_id' => 'required',
            'site_ids' => 'required',
            'ip' => 'required',
            'username' => 'required',
            'password' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'language_id.required' => '语言ID不能为空',
            'site_ids.required' => '站点ID不能为空',
            'ip.required' => 'IP不能为空',
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
            'name' => 'required',
            'language_id' => 'required',
            'site_ids' => 'required',
            'ip' => 'required',
            'username' => 'required',
            'password' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
            'language_id.required' => '语言ID不能为空',
            'site_ids.required' => '站点ID不能为空',
            'ip.required' => 'IP不能为空',
            'username.required' => '用户名不能为空',
            'password.required' => '密码不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
