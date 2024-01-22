<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class VideoRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'title' => 'required',
            'content' => 'required',
        ];
        $message = [
            'title.required' => '名称不能为空',
            'content.required' => '登陆名不能为空',
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
            'content' => 'required',
        ];
        $message = [
            'id' => 'required',
            'title.required' => '名称不能为空',
            'content.required' => '登陆名不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
