<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class LanguageRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
        ];
        $meassge = [
            'name.required' => '名称不能为空',
        ];
        return $this->validateRequest($request, $rules,$meassge);
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
        ];
        $meassge = [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
        ];
        return $this->validateRequest($request, $rules,$meassge);
    }
}
