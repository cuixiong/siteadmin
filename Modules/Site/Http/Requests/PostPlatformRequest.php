<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class PostPlatformRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'keywords' => 'required|unique:post_platform,keywords',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'keywords.unique' => '域名不能重复',
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
            'name' => 'required',
            'keywords' => [
                'required',
                \Illuminate\Validation\Rule::unique('post_platform')->ignore($request->input('id')),
            ],
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
            'keywords.unique' => '域名不能重复',
        ];
        return $this->validateRequest($request, $rules, $message);
    }
}
