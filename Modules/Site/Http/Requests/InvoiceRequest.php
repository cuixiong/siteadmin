<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;
use Modules\Site\Http\Rules\PhoneCheck;

class InvoiceRequest extends BaseRequest {
    /**
     * 更新数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function update($request) {
        $rules = [
            'id'           => 'required',
            'price'        => 'numeric|between:0.01,99999999.99',
            //'phone'        => [new PhoneCheck()],
            'bank_name'    => 'max:25',
            'bank_account' => 'max:25',
        ];
        $message = [
            'id.required'      => 'id不能为空',
            'price.between'    => '金额必须在:min - :max之间',
            ///'phone.mobile'     => '手机号格式错误',
            'bank_name.max'    => '银行昵称超出最大长度',
            'bank_account.max' => '银行账号超出最大长度',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
