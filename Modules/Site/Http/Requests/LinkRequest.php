<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class LinkRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            //'logo' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
//            'logo.required' => 'logo图片不能为空',
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
//            'logo' => 'required',
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
//            'logo.required' => 'logo图片不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
