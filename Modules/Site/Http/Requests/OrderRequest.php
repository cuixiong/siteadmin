<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;
use Modules\Site\Http\Rules\ValueInRange;

class OrderRequest extends BaseRequest {
    /**
     * 更新数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function update($request) {
        $rules = [
            'id'            => 'required',
            'actually_paid' => 'numeric|between:0.01,999999.99'
        ];
        $message = [
            'id.required'           => 'id不能为空',
            'actually_paid.between' => '金额必须在:min - :max之间',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
