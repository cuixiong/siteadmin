<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class TimedTaskRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'content' => 'required|unique:timed_tasks',
        ];
        $message = [
            'name.required' => '用户名不能为空',
            'type.required' => '类型不能为空',
            'content.required' => '执行脚本内容不能为空',
            'content.unique' => '执行脚本内容已存在，请更换其他执行脚本内容',
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
            'type' => 'required',
            'content' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '用户名不能为空',
            'type.required' => '类型不能为空',
            'content.required' => '执行脚本内容不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }

    /**
     * 个人信息修改验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function updateInfo($request)
    {
        $rules = [
            'nickname' => 'required',
        ];
        $message = [
            'nickname.required' => trans('lang.nickname_empty'),
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
