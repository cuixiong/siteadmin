<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class HistoryRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'year' => 'required|size:4',
            'body' => 'required',
            'status' => 'required',
        ];
        $message = [
            'year.required' => '年份不能为空',
            'year.size' => '年份不正确',
            'body.required' => '发展事件不能为空',
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
            'year' => 'required|size:4',
            'body' => 'required',
            'status' => 'required',
        ];
        $message = [
            'id' => 'required',
            'year.required' => '年份不能为空',
            'year.size' => '年份不正确',
            'body.required' => '发展事件不能为空',
            'status.required' => '状态不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
