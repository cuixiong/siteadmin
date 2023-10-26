<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class RoleRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'status' => 'required',
            'data_scope' => 'required',
            'code' => 'required',
            'sort' => 'required',
            'status' => 'required',
        ];
        return $this->validateRequest($request, $rules);
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
            'status' => 'required',
            'data_scope' => 'required',
            'code' => 'required',
            'sort' => 'required',
            'status' => 'required',
        ];
        return $this->validateRequest($request, $rules);
    }
}
