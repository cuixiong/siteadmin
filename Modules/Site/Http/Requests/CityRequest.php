<?php
namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;

class CityRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'country_id' => 'required',
            'type' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'country_id.required' => '所属国家不能为空',
            'type.required' => '行政级别不能为空',
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
            'country_id' => 'required',
            'type' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
            'country_id.required' => '所属国家不能为空',
            'type.required' => '行政级别不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
