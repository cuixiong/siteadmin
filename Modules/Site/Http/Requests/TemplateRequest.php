<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class TemplateRequest extends BaseRequest {
    /**
     * 列表数据校验
     *
     * @param \Illuminate\Http\Request $request
     */
    public function list($request) {
        $rules = [
            'type' => 'required',
        ];
        $message = [
            'type.required' => '类型不能为空',
        ];

        return $this->validateRequest($request, $rules, $message);
    }

    public function copyWordByTemplate($request) {
        $rules = [
            'templateId' => 'required',
            'productId'  => 'required',
        ];
        $message = [
            'templateId.required' => '模板不能为空',
            'productId.required'  => '报告不能为空',
        ];

        return $this->validateRequest($request, $rules, $message);
    }

    /**
     * 新增数据验证
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store($request) {
        $rules = [
            'name'      => 'required',
            'type'      => 'required',
            'cate_ids'  => 'required',
            'btn_color' => 'required',
            //'content'   => 'required',
        ];
        $message = [
            'name.required'      => '模版昵称不能为空',
            'type.required'      => '类型不能为空',
            'cate_ids.required'  => '模版分类不能为空',
            'btn_color.required' => '按钮颜色不能为空',
            //'content.required'   => '模版内容不能为空',
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
            'id'        => 'required',
            'name'      => 'required',
            //'type'      => 'required',
            'cate_ids'  => 'required',
            'btn_color' => 'required',
            //'content'   => 'required',
        ];
        $message = [
            'id.required'        => 'id不能为空',
            'name.required'      => '模版昵称不能为空',
            //'type.required'      => '类型不能为空',
            'cate_ids.required'  => '模版分类不能为空',
            'btn_color.required' => '按钮颜色不能为空',
            //'content.required'   => '模版内容不能为空',
        ];

        return $this->validateRequest($request, $rules, $message);
    }
}
