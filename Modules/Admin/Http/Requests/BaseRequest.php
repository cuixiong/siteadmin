<?php
namespace Modules\Admin\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
/**
 * Admin模块基类表单验证器
 */
class BaseRequest extends FormRequest
{
    /**
     * 验证表单数据中间方法
     * @param $request 表单数据
     * @param $rules 验证规则
     * @param $message 错误提示
     */
    protected function validateRequest($request, $rules = [],$message = [])
    {
        $Validate = Validator::make($request->all(), $rules,$message)->validate();
        return $Validate;
    }

    /**
     * 验证表单数据
     * @param  \Illuminate\Http\Request  $request
     */
    public function DoVlidate($request)
    {
        // 获取路由请求的方法名称
        $action = $request->route()->getActionName();
        list($class, $action) = explode('@', $action);
        // 执行验证数据方法
        $res = $this->$action($request);

        return $res;
    }

    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        return $this->validateRequest($request, []);
    }
    /**
     * 删除数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function destroy($request)
    {
        return $this->validateRequest($request, ['ids' => 'required'],['ids.required' => 'ID不能为空！']);
    }
    /**
     * 更新数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function update($request)
    {
        return $this->validateRequest($request, []);
    }
    /**
     * 查询数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function one($request)
    {
        return $this->validateRequest($request, []);
    }
    /**
     * 查询列表数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function list($request)
    {
        return $this->validateRequest($request, []);
    }
}
