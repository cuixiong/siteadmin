<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class PositionRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'role_id' => 'required'
        ];
        $message = [
            'name.required' => '名称不能为空',
            'role_id' => '默认角色ID不能为空'
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
            'name' => 'required',
            'role_id' => 'required'
        ];
        $message = [
            'name.required' => '名称不能为空',
            'role_id' => '默认角色ID不能为空'
        ];
        return $this->validateRequest($request,$rules, $message);
    }
}
