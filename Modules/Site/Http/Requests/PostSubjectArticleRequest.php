<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class PostSubjectArticleRequest extends BaseRequest
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
        ];
        $message = [
            'name.required' => '名称不能为空',
            'name.unique'   => '名称不能重复',
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
            'name'           => [
                'required',
                \Illuminate\Validation\Rule::unique('post_subject_article')->ignore($request->input('id')),
            ],
            'name' => 'required',
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
            'name.unique'   => '名称不能重复'.$request->input('id'),
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
