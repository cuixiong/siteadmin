<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class TemplateCategoryRequest extends BaseRequest {
    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
            'name'        => 'required',
            //'match_words' => 'required',
            'sort'        => 'required',
            'status'      => 'required',
        ];
        $message = [
            'name.required'        => '名称不能为空',
            //'match_words.required' => '匹配词不能为空',
            'sort.required'        => '排序不能为空',
            'status.required'      => '状态不能为空',
        ];

        return $this->validateRequest($request, $rules, $message);
    }

    /**
     * 更新数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function update($request) {
        $rules = [
            'id'          => 'required',
            'name'        => 'required',
            //'match_words' => 'required',
            'sort'        => 'required',
            'status'      => 'required',
        ];
        $message = [
            'id.required'          => 'id不能为空',
            'name.required'        => '名称不能为空',
            //'match_words.required' => '匹配词不能为空',
            'sort.required'        => '排序不能为空',
            'status.required'      => '状态不能为空',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
