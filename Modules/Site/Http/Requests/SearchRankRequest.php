<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class SearchRankRequest extends BaseRequest {
    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
            'name' => 'required|unique:search_ranks,name',
            'sort' => 'numeric|between:0,127',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'name.unique'   => '名称不能重复',
            'sort.between'  => '排序必须在:min - :max之间',
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
            'id'   => 'required',
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('search_ranks')->ignore($request->input('id')),
            ],
            'sort' => 'numeric|between:0,127',
            'hits' => 'numeric|between:0,999999999',
        ];
        $message = [
            'id.required'   => 'ID不能为空',
            'name.required' => '名称不能为空',
            'name.unique'   => '名称不能重复',
            'sort.between'  => '排序必须在:min - :max之间',
            'hits.between'  => '次数必须在:min - :max之间',
        ];

        return $this->validateRequest($request, $rules, $message);
    }

    /**
     * 修改排序数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function changeSort($request) {
        $rules = [
            'id'   => 'required',
            'sort' => 'numeric|between:0,127',
        ];
        $message = [
            'id.required'  => 'ID不能为空',
            'sort.between' => '排序必须在:min - :max之间',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
