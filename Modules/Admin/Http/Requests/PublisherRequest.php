<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class PublisherRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'email' => 'required',
            'phone' => 'required',
            'company' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'name' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'phone.required' => '手机不能为空',
            'company.required' => '公司不能为空',
            'province_id.required' => '省份不能为空',
            'city_id.required' => '城市不能为空',
            'email.required' => '邮箱不能为空',
        ];
        return $this->validateRequest($request,$rules, $message);
    }

    /**
     * 更新数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function update($request)
    {
        $rules = [
            'email' => 'required',
            'phone' => 'required',
            'company' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'name' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'phone.required' => '手机不能为空',
            'company.required' => '公司不能为空',
            'province_id.required' => '省份不能为空',
            'city_id.required' => '城市不能为空',
            'email.required' => '邮箱不能为空',
        ];
        return $this->validateRequest($request,$rules, $message);
    }
}
