<?php

namespace Modules\Admin\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class PriceEditionValueRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'edition_id' => 'required',
            'language_id' => 'required',
            'rules' => 'required',
        ];
        $message = [
            'name.required' => '版本项名称不能为空',
            'edition_id.required' => '所属版本不能为空',
            'language_id.required' => '语言不能为空',
            'rules.required' => '规则不能为空',
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
            'name' => 'required',
            'edition_id' => 'required',
            'language_id' => 'required',
            'rules' => 'required',
        ];
        $message = [
            'name.required' => '版本项名称不能为空',
            'edition_id.required' => '所属版本不能为空',
            'language_id.required' => '语言不能为空',
            'rules.required' => '规则不能为空',
        ];
        return $this->validateRequest($request, $rules, $message);
    }
}
