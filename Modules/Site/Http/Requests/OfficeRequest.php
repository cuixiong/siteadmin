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
            'city' => 'required',
            'name' => 'required',
            'language_alias' => 'required',
            'region' => 'required',
            'area' => 'required',
            'image' => 'required',
            'national_flag' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'working_language' => 'required',
            'working_time' => 'required',
            'time_zone' => 'required',
        ];
        $message = [
            'city.required' => '城市不能为空',
            'name.required' => '简称不能为空',
            'language_alias.required' => '语言别名不能为空',
            'region.required' => '区域不能为空',
            'area.required' => '地区不能为空',
            'image.required' => '图片不能为空',
            'national_flag.required' => '国旗不能为空',
            'phone.required' => '电话不能为空',
            'address.required' => '地址不能为空',
            'working_language.required' => '工作语言不能为空',
            'working_time.required' => '不能为空',
            'time_zone.required' => '时区不能为空',
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
            'city' => 'required',
            'name' => 'required',
            'language_alias' => 'required',
            'region' => 'required',
            'area' => 'required',
            'image' => 'required',
            'national_flag' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'working_language' => 'required',
            'working_time' => 'required',
            'time_zone' => 'required',
        ];
        $message = [
            'id' => 'required',
            'city.required' => '城市不能为空',
            'name.required' => '简称不能为空',
            'language_alias.required' => '语言别名不能为空',
            'region.required' => '区域不能为空',
            'area.required' => '地区不能为空',
            'image.required' => '图片不能为空',
            'national_flag.required' => '国旗不能为空',
            'phone.required' => '电话不能为空',
            'address.required' => '地址不能为空',
            'working_language.required' => '工作语言不能为空',
            'working_time.required' => '不能为空',
            'time_zone.required' => '时区不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}