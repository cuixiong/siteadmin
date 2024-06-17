<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class SyncThirdProductRequest extends BaseRequest {
    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
        ];
        $message = [
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
        ];
        $message = [
        ];

        return $this->validateRequest($request, $rules, $message);
    }

    public function form($request) {
        $rules = [
        ];
        $message = [
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
