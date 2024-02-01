<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class OfficeRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'language_alias' => 'required',
        ];
        $message = [
            'name.required' => '简称不能为空',
            'language_alias.required' => '语言别名不能为空',
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
            'language_alias' => 'required',
        ];
        $message = [
            'id' => 'required',
            'name.required' => '简称不能为空',
            'language_alias.required' => '语言别名不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}