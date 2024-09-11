<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class PriceEditionRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'publisher_id' => 'required',
        ];
        $message = [
            'publisher_id.required' => '出版商不能为空',
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
            'publisher_id' => 'required',
        ];
        $message = [
            'publisher_id.required' => '出版商不能为空',
        ];
        return $this->validateRequest($request, $rules, $message);
    }
}
