<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class CouponRequest extends BaseRequest
{
/**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'code' => 'required|unique:coupons,code',
        ];
        $message = [
            'code.required' => '名称不能为空',
            'code.unique' => '名称不能重复',
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
            'code' => [
                'required',
                \Illuminate\Validation\Rule::unique('coupons')->ignore($request->input('id')),
            ]
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'code.required' => '名称不能为空',
            'code.unique' => '名称不能重复',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
