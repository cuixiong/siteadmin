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
            'status' => 'required',
            'do_command' => 'required',
        ];
        $message = [
            'name.required' => '用户名不能为空',
            'type.required' => '类型不能为空',
            'status.required' => '状态不能为空',
            'do_command.required' => '执行脚本内容不能为空',
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
            'status' => 'required',
            'do_command' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '用户名不能为空',
            'type.required' => '类型不能为空',
            'status.required' => '状态不能为空',
            'do_command.required' => '执行脚本内容不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
