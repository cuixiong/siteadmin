<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class PayRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'image' => 'required',
            'info_logo' => 'required',
            'info_key' => 'required',
            'return_url' => 'required',
            'notify_url' => 'required',
            'sign' => 'required',
            'status' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'image.required' => '支付图片不能为空',
            'info_logo.required' => 'info_logo不能重复',
            'info_key.required' => 'info_key不能为空',
            'return_url.required' => '同步回调地址不能为空',
            'notify_url.required' => '异步回调地址不能为空',
            'sign.required' => '回调签名',
            'status.required' => '状态不能为空',
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
            'image' => 'required',
            'info_logo' => 'required',
            'info_key' => 'required',
            'return_url' => 'required',
            'notify_url' => 'required',
            'sign' => 'required',
            'status' => 'required',
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
            'image.required' => '支付图片不能为空',
            'info_logo.required' => 'info_logo不能重复',
            'info_key.required' => 'info_key不能为空',
            'return_url.required' => '同步回调地址不能为空',
            'notify_url.required' => '异步回调地址不能为空',
            'sign.required' => '回调签名',
            'status.required' => '状态不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
