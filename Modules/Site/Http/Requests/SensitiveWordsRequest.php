<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class SensitiveWordsRequest extends BaseRequest {
    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
            'word' => 'required',
        ];
        $message = [
            'word.required' => '敏感词不能为空',
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
            'word' => 'required',
        ];
        $message = [
            'word.required' => '敏感词不能为空',
        ];

        return $this->validateRequest($request, $rules, $message);
    }

    public function changeStatus($request) {
        $rules = [
            'id'     => 'required',
            'status' => 'required',
        ];
        $message = [
            'id.required'     => 'id不能为空',
            'status.required' => '状态不能为空',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
