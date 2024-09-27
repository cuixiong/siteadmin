<?php
namespace Modules\Site\Http\Requests;
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
            //'email_recipient' => 'required',
            'status' => 'required',
            'action' => 'required|unique:email_scenes',
            'alternate_email_id' => 'required|different:email_sender_id',
        ];
        $message = [
            'name.required' => '场景名称不能为空',
            'title.required' => '邮箱标题不能为空',
            'body.required' => '邮箱内容不能为空',
            'email_sender_id.required' => '发送邮件的邮箱ID不能为空',
            //'email_recipient.required' => '邮箱收件人不能为空',
            'status.required' => '状态不能为空',
            'action.required' => trans('lang.action_empty'),
            'action.unique' => trans('lang.action_unique'),
            'alternate_email_id.required' => trans('lang.alternate_email_empty'),
            'alternate_email_id.different' => '发送邮箱与备用邮箱不能相同',
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
            'title' => 'required',
            'body' => 'required',
            'email_sender_id' => 'required',
            //'email_recipient' => 'required',
            'status' => 'required',
            'action' => [
                'required',
                \Illuminate\Validation\Rule::unique('email_scenes')->ignore($request->input('id')),
            ],
            'alternate_email_id' => 'required|different:email_sender_id',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '场景名称不能为空',
            'title.required' => '邮箱标题不能为空',
            'body.required' => '邮箱内容不能为空',
            'email_sender_id.required' => '发送邮件的邮箱ID不能为空',
            //'email_recipient.required' => '邮箱收件人不能为空',
            'status.required' => '状态不能为空',
            'action.required' => trans('lang.action_empty'),
            'action.unique' => trans('lang.action_unique'),
            'alternate_email_id.required' => trans('lang.alternate_email_empty'),
            'alternate_email_id.different' => '发送邮箱与备用邮箱不能相同',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
