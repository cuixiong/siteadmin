<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class ApplyforRequest extends BaseRequest
{
    /**
     * 更新数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function update($request)
    {
        $rules = [
            'id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'company' => 'required',
            'country' => 'required',
            'status' => 'required',
            'source' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
            'email.required' => '邮箱不能为空',
            'company.required' => '公司不能为空',
            'country.required' => '国家不能为空',
            'status.required' => '状态不能为空',
            'source.required' => '来源不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
