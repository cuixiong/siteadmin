<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class RuleRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'vue_route' => 'required|unique:rules',
            'type' => 'required',
            'category' => 'required',
        ];
        $message = [
            'name.required' => '用户名不能为空',
            'vue_route.required' => '前端路由不能为空',
            'vue_route.unique' => '前端路由已存在，请更换其他前端路由',
            'type.required' => '权限类型不能为空',
            'category.required' => '权限类别不能为空',
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
            'vue_route' => 'required',
            'type' => 'required',
            'category' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '用户名不能为空',
            'vue_route.required' => '前端路由不能为空',
            'type.required' => '权限类型不能为空',
            'category.required' => '权限类别不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
