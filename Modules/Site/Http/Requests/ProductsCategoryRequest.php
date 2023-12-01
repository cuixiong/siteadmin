<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class ProductsCategoryRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required|unique:product_category,name',
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
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('product_category')->ignore($request->input('id')),
            ]
        ];
        $message = [
            'name.required' => '名称不能为空',
            'name.unique' => '名称不能重复',
        ];
        return $this->validateRequest($request, $rules, $message);
    }

    /**
     * 修改折扣验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function discount($request)
    {
        $rules = [
            'discount_type' => 'required|numeric|in:1,2',
            'discount' => 'numeric|between:0,100', // discount 在 0 到 100 之间
            'discount_amount' => 'numeric|min:0', // discount_amount 大于等于 0

        ];
        $message = [
            'discount_type.required' => '折扣类型不能为空',
            'discount_type.numeric' => '折扣类型必须为数字',
            'discount_type.in' => '折扣类型范围不合法',
            'discount.numeric' => '折扣率需为数字',
            'discount.between' => '折扣率范围在0-100之间',
            'discount_amount.numeric' => '折扣金额需为数字',
            'discount_amount.min' => '折扣金额最小为0',
        ];
        return $this->validateRequest($request, $rules, $message);
    }
}
