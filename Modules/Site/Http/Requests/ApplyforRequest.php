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
        ];
        $message = [
            'id.required' => 'ID不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
