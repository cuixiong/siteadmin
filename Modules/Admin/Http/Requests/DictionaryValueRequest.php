<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class DictionaryValueRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'value' => 'required',
            'parent_id' => 'required',
            'status' => 'required',
            'code' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'value.required' => '编码不能为空',
            'parent_id.required' => '父级ID不能为空',
            'status.required' => '状态不能为空',
            'code.required' => '编码不能为空',
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
            'value' => 'required',
            'parent_id' => 'required',
            'status' => 'required',
            'code' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'value.required' => '编码不能为空',
            'parent_id.required' => '父级ID不能为空',
            'status.required' => '状态不能为空',
            'code.required' => '编码不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
