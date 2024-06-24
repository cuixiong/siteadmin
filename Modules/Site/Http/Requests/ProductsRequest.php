<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;
use Modules\Site\Http\Rules\SensitiveWord;
use Modules\Site\Http\Rules\UppercaseRule;
use Modules\Site\Http\Rules\ValueInRange;

class ProductsRequest extends BaseRequest {
    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
            'name'           => ['required', 'unique:product_routine,name', new SensitiveWord()],
            'published_date' => 'required',
            'keywords'       => 'required',
            'url'            => 'required',
            'price'          => ['required', 'numeric', 'between:0.01,999999.99'],
            'tables'         => 'numeric|between:0,32767',
            'pages'          => 'numeric|between:0,32767',
            'sort'           => new ValueInRange(0, 127),
        ];
        $message = [
            'name.required'                             => '名称不能为空',
            'name.unique'                               => '名称不能重复',
            'published_date.required'                   => '出版时间不能为空',
            'keywords.required'                         => '关键词不能为空',
            'url.required'                              => '自定义链接不能为空',
            'price.required'                            => '基础价不能为空',
            'price.between'                             => '价格必须在:min - :max之间',
            'tables.between'                            => '表格必须在:min - :max之间',
            'pages.between'                             => '页数必须在:min - :max之间',
            'sort.Modules\Site\Http\Rules\ValueInRange' => "排序必须在0 - 127之间",
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
            'name'           => [
                'required',
                \Illuminate\Validation\Rule::unique('product_routine')->ignore($request->input('id')),
                //new SensitiveWord()
            ],
            'category_id'    => 'required',
            'published_date' => 'required',
            'keywords'       => 'required',
            'url'            => 'required',
            'price'          => ['required', 'numeric', 'between:0.01,999999.99'],
            'tables'         => 'numeric|between:0,32767',
            'pages'          => 'numeric|between:0,32767',
            'sort'           => new ValueInRange(0, 127),
        ];
        $message = [
            'name.required'                             => '名称不能为空',
            'name.unique'                               => '名称不能重复',
            'name.sensitive_word'                       => '该报告含有敏感词',
            'category_id.required'                      => '报告分类不能为空',
            'published_date.required'                   => '出版时间不能为空',
            'keywords.required'                         => '关键词不能为空',
            'url.required'                              => '自定义链接不能为空',
            'price.required'                            => '基础价不能为空',
            'price.between'                             => '价格必须在:min - :max之间',
            'sort.Modules\Site\Http\Rules\ValueInRange' => "排序必须在0 - 127之间",
            'tables.between'                            => '表格必须在:min - :max之间',
            'pages.between'                             => '页数必须在:min - :max之间',
        ];

        return $this->validateRequest($request, $rules, $message);
    }

    /**
     * 修改折扣验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function discount($request) {
        $rules = [
            'discount_type'   => 'required|numeric|in:1,2',
            'discount'        => 'numeric|between:0,100', // discount 在 0 到 100 之间
            'discount_amount' => 'numeric|min:0', // discount_amount 大于等于 0
        ];
        $message = [
            'discount_type.required'  => '折扣类型不能为空',
            'discount_type.numeric'   => '折扣类型必须为数字',
            'discount_type.in'        => '折扣类型范围不合法',
            'discount.numeric'        => '折扣率需为数字',
            'discount.between'        => '折扣率范围在0-100之间',
            'discount_amount.numeric' => '折扣金额需为数字',
            'discount_amount.min'     => '折扣金额最小为0',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
