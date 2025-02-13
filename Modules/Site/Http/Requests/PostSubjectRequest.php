<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class PostSubjectRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            // 'name' => ['required', 'unique:post_subject,name',],
            'name' => 'required',
            'product_id' => ['required', 'unique:post_subject,product_id'],
        ];
        $message = [
            'name.required' => '名称不能为空',
            'product_id.required'   => '报告不能为空',
            'product_id.unique'   => '报告不能重复',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
    /**
     * 更新数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function update($request)
    {
        $rules = [
            'id' => 'required',
            // 'name'           => [
            //     'required',
            //     \Illuminate\Validation\Rule::unique('post_subject')->ignore($request->input('id')),
            // ],
            'name' => 'required',
            'product_id'           => [
                'required',
                \Illuminate\Validation\Rule::unique('post_subject')->ignore($request->input('id')),
            ],
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
            'product_id.required'   => '报告不能为空',
            'product_id.unique'   => '报告不能重复'.$request->input('id'),
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
