<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;
use Modules\Site\Http\Rules\ValueInRange;

class ProductsExcelFieldRequest extends BaseRequest {
    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
            'name' => 'required',
            'sort' => [new ValueInRange(0, 32767)],
        ];
        $message = [
            'name.required' => '名称不能为空',
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
            'name' => 'required',
            'sort' => 'numeric|between:0,32767',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'sort.between'  => '排序必须在:min - :max之间',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
