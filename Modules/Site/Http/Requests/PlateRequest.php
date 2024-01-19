<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class PlateRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'page_id' => 'required',
            'title' => 'required',
            'short_title' => 'required',
            'pc_image' => 'required',
            'mb_image' => 'required',
            'content' => 'required',
            'status' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'page_id.required' => '页面ID不能为空',
            'title.unique' => '标题不能重复',
            'short_title.required' => '短标题不能为空',
            'pc_image.required' => 'PC图片不能为空',
            'mb_image.required' => '手机图片不能为空',
            'content.required' => '内容不能为空',
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
            'name' => 'required',
            'page_id' => 'required',
            'title' => 'required',
            'short_title' => 'required',
            'pc_image' => 'required',
            'mb_image' => 'required',
            'content' => 'required',
            'status' => 'required',
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
            'page_id.required' => '页面ID类型不能为空',
            'title.unique' => '标题不能重复',
            'short_title.required' => '短标题不能为空',
            'pc_image.required' => 'PC图片不能为空',
            'mb_image.required' => '手机图片不能为空',
            'content.required' => '内容不能为空',
            'status.required' => '状态不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
