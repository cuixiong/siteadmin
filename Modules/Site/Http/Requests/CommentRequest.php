<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class CommentRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'title' => 'required',
            'image' => 'required',
            'status' => 'required',
        ];
        $message = [
            'title.required' => '标题不能为空',
            'image.required' => '图片不能为空',
            'status.required' => '状态不能为空',
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
            'title' => 'required',
            'image' => 'required',
            'status' => 'required',
        ];
        $message = [
            'id' => 'required',
            'title.required' => '标题不能为空',
            'image.required' => '图片不能为空',
            'status.required' => '状态不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
