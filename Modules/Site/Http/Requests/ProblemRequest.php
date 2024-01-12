<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class ProblemRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'problem' => 'required',
            'reply' => 'required',
            'status' => 'required',
        ];
        $message = [
            'problem.required' => '问题不能为空',
            'reply.required' => '回复不能为空',
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
            'problem' => 'required',
            'reply' => 'required',
            'status' => 'required',
        ];
        $message = [
            'id' => 'required',
            'problem.required' => '问题不能为空',
            'reply.required' => '回复不能为空',
            'status.required' => '状态不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
