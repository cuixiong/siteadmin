<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class InformationRequest extends BaseRequest {
    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
            'title'       => 'required',
            //'type'        => 'required',
            'description' => 'required',
            'url'         => 'required',
            'upload_at'   => 'required',
        ];
        $message = [
            'title.required'       => '标题不能为空',
            //'type.required'        => '类型不能为空',
            'description.required' => '描述不能为空',
            'url.required'         => '自定义链接不能为空',
            'upload_at.required'   => '上传时间不能为空',
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
            'title'       => 'required',
//            'type'        => 'required',
            'description' => 'required',
            'url'         => 'required',
            'upload_at'   => 'required',
        ];
        $message = [
            'title.required'       => '标题不能为空',
//            'type.required'        => '类型不能为空',
            'description.required' => '描述不能为空',
            'url.required'         => '自定义链接不能为空',
            'upload_at.required'   => '上传时间不能为空',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
