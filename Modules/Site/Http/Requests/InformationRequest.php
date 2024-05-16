<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;
use Modules\Site\Http\Rules\ValueInRange;

class InformationRequest extends BaseRequest {
    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
            'title'       => 'required',
            'description' => 'required',
            'url'         => 'required',
            'upload_at'   => 'required',
            'sort'        => 'numeric|between:0,32767',
            'hits'        => new ValueInRange(1, 99999999),
        ];
        $message = [
            'title.required'                            => '标题不能为空',
            'description.required'                      => '描述不能为空',
            'url.required'                              => '自定义链接不能为空',
            'upload_at.required'                        => '上传时间不能为空',
            'sort.between'                              => '排序必须在:min - :max之间',
            'hits.Modules\Site\Http\Rules\ValueInRange' => '点击数必须在1 - 99999999之间',
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
            'sort'        => 'numeric|between:0,32767',
            'hits'        => 'numeric|between:1, 99999999',
        ];
        $message = [
            'title.required'       => '标题不能为空',
            'description.required' => '描述不能为空',
            'url.required'         => '自定义链接不能为空',
            'upload_at.required'   => '上传时间不能为空',
            'sort.between'         => '排序必须在:min - :max之间',
            'hits.between'         => '点击数必须在:min - :max之间',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
