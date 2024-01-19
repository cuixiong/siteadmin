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
        ];
        $message = [
            'name.required' => '名称不能为空',
            'page_id.required' => '页面ID不能为空',
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
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
            'page_id.required' => '页面ID类型不能为空',
            'title.required' => '标题不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
