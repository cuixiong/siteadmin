<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class PageRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'page_id' => 'required',
            'content' => 'required',
        ];
        $message = [
            'page_id.required' => '页面ID不能为空',
            'content.required' => '页面内容不能为空',
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
            'page_id' => 'required',
            'content' => 'required',
        ];
        $message = [
            'id' => 'required',
            'page_id.required' => '页面ID不能为空',
            'content.required' => '页面内容不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
