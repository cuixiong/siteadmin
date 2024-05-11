<?php

namespace Modules\Admin\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class RegionRequest extends BaseRequest {
    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
            'name' => 'required|unique:regions,name',
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
                \Illuminate\Validation\Rule::unique('regions')->ignore($request->input('id')),
            ]
        ];
        $message = [
            'id.required'   => 'ID不能为空',
            'name.required' => '名称不能为空',
            'name.unique'   => '名称不能重复',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
