<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class NewsCategoryRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required|unique:news_category',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'name.unique' => '名称不能重复',
        ];
        return $this->validateRequest($request, $rules, $message);
    }
    /**
     * 更新数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function update($request)
    {
        $rules = [
            'id' => 'required',
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('news_category')->ignore($request->input('id')),
            ]
        ];
        $message = [
            'id.required' => 'id不能为空',
            'name.required' => '名称不能为空',
            'name.unique' => '名称不能重复',
        ];
        return $this->validateRequest($request, $rules, $message);
    }
}
