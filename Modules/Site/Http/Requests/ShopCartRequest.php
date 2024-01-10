<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class ShopCartRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'goods_id' => 'required',
            'number' => 'required|integer|min:0',
            'price_edition' => 'required',
        ];

        $message = [
            'goods_id.required' => '报告id不能为空',
            'number.required' => '数量不能为空',
            'number.integer' => '数量需为正整数',
            'price_edition.required' => '版本不能为空',
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
            'goods_id' => 'required',
            'number' => 'required|integer|min:0',
            'price_edition' => 'required',
        ];

        $message = [
            'goods_id.required' => '报告id不能为空',
            'number.required' => '数量不能为空',
            'number.integer' => '数量需为正整数',
            'price_edition.required' => '版本不能为空',
        ];
        return $this->validateRequest($request, $rules, $message);
    }
}
