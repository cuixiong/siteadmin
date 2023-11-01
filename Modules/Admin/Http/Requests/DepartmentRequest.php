<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class DepartmentRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'parent_id' => 'required',
            'name' => 'required',
            'default_role' => 'required',
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
            'parent_id' => 'required',
            'name' => 'required',
            'default_role' => 'required',
        ];
        return $this->validateRequest($request, $rules);
    }
}
