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
        ];
        $message = [
            'parent_id.required' => '父级ID不能为空',
            'name.required' => '名称不能为空',
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
            'parent_id' => 'required',
            'name' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'parent_id.required' => '父级ID不能为空',
            'name.required' => '名称不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
