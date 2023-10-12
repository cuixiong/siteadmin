<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class EmailSceneRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'title' => 'required',
            'body' => 'required',
            'email_sender_id' => 'required',
            'email_recipient' => 'required',
            'status' => 'required',
        ];
        $meassge = [
            'name.required' => '场景名称不能为空',
            'title.required' => '邮箱标题不能为空',
            'body.required' => '邮箱内容不能为空',
            'email_sender_id.required' => '发送邮件的邮箱ID不能为空',
            'email_recipient.required' => '邮箱收件人不能为空',
            'status.required' => '状态不能为空',
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
            'title' => 'required',
            'body' => 'required',
            'email_sender_id' => 'required',
            'email_recipient' => 'required',
            'status' => 'required',
        ];
        $meassge = [
            'id.required' => 'ID不能为空',
            'name.required' => '场景名称不能为空',
            'title.required' => '邮箱标题不能为空',
            'body.required' => '邮箱内容不能为空',
            'email_sender_id.required' => '发送邮件的邮箱ID不能为空',
            'email_recipient.required' => '邮箱收件人不能为空',
            'status.required' => '状态不能为空',
        ];
        return $this->validateRequest($request, $rules,$meassge);
    }
}
