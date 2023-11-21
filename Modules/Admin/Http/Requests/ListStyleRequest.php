<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class ListStyleRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'user_id' => 'required',
        ];
        $message = [
            'name.required' => '用户不能为空',
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
            'user_id' => 'required',
        ];
        $message = [
            'name.required' => '用户不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
