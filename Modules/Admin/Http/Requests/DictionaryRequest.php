<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class DictionaryRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'code' => 'required|unique:dictionaries',
            'status' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'code.required' => '编码不能为空',
            'status.required' => '状态不能为空',
            'code.unique' => trans('lang.code_exists'),
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
            'name' => 'required',
            'code' => 'required',
            'status' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'code.required' => '编码不能为空',
            'status.required' => '状态不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
