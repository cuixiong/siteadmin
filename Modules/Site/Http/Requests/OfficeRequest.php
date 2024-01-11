<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class TeamMemberRequest extends BaseRequest
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
            'address' => 'required',
            'phone' => 'required',
            'wechat' => 'required',
            'language' => 'required',
            'work_time' => 'required',
            'status' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'image.required' => '图片不能为空',
            'address.required' => '地址不能为空',
            'phone.required' => '电话不能为空',
            'wechat.required' => '电话/微信不能为空',
            'language.required' => '语言支持不能为空',
            'work_time.required' => '工作时间不能为空',
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
            'address' => 'required',
            'phone' => 'required',
            'wechat' => 'required',
            'language' => 'required',
            'work_time' => 'required',
            'status' => 'required',
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
            'image.required' => '图片不能为空',
            'address.required' => '地址不能为空',
            'phone.required' => '电话不能为空',
            'wechat.required' => '电话/微信不能为空',
            'language.required' => '语言支持不能为空',
            'work_time.required' => '工作时间不能为空',
            'status.required' => '状态不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}