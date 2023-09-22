<?php
namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;

class CrudController extends Controller
{
    protected $model; // 模型类名:若没有指定模型，则根据控制器名找到对应的模型
    protected $action; // 请求方法名称
    public function __construct()
    {
        // 模型类名:若没有指定模型，则根据控制器名找到对应的模型
        if(empty($this->model)){
            $Controller = (new \ReflectionClass($this))->getShortName();// 控制器名
            $model = str_replace('Controller','',$Controller);
            $model = 'Modules\Admin\Http\Models\\'.$model;
            $this->model = $model;
            // 获取当前请求的方法名
            list($class, $this->action) = explode('@', \Route::current()->getActionName());
        }
    }
    /**
     *  获取模型实例
     */
    protected function ModelInstance()
    {
        return new $this->model();
    }

    /**
     * 获取表单验证规则
     */
    protected function ValidationRules(){
        // 获取表单验证规则
        $rule = $this->ModelInstance()->FieldRule;
        $rule = isset($rule[$this->action]) ? $rule[$this->action] : [];
        return $rule;
    }

    /**
     * 获取表单验证提示语
     */
    protected function ValidationMes(){
        // 获取表单验证提示语
        $message = $this->ModelInstance()->FieldMessage;
        $message = isset($message[$this->action]) ? $message[$this->action] : [];
        return $message;
    }

    /**
     * AJax单行删除
     * @param $id 主键ID
     */
    protected function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), $this->ValidationRules(),$this->ValidationMes());
        if ($validator->fails()) {
            ReturnJson(FALSE,$validator->errors()->first());
        }
        $record = $this->ModelInstance()->findOrFail($request->id);
        if(!$record->delete()){
            ReturnJson(FALSE,'删除失败');
        }
        ReturnJson(TRUE,'删除成功');
    }

    /**
     * AJax单个更新
     * @param $request 请求信息
     */
    protected function update(Request $request)
    {
        $validator = Validator::make($request->all(), $this->ValidationRules(),$this->ValidationMes());
        if ($validator->fails()) {
            ReturnJson(FALSE,$validator->errors()->first());
        }
        $input = $request->all();
        $record = $this->ModelInstance()->findOrFail($request->id);
        if(!$record->update($input)){
            ReturnJson(FALSE,'更新失败');
        }
        ReturnJson(TRUE,'更新成功');
    }

    /**
     * AJax单个保存
     * @param $request 请求信息
     */
    protected function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->ValidationRules(),$this->ValidationMes());
        if ($validator->fails()) {
            ReturnJson(FALSE,$validator->errors()->first());
        }
        $input = $request->all();
        $record = $this->ModelInstance()->create($input);
        if(!$record){
            ReturnJson(FALSE,'更新失败');
        }
        ReturnJson(TRUE,'更新成功',['id' => $record->id]);
    }

    /**
     * AJax单个查询
     * @param $request 请求信息
     */
    protected function one(Request $request)
    {
        $validator = Validator::make($request->all(), $this->ValidationRules(),$this->ValidationMes());
        if ($validator->fails()) {
            ReturnJson(FALSE,$validator->errors()->first());
        }
        $record = $this->ModelInstance()->findOrFail($request->id);
        ReturnJson(TRUE,'请求成功',$record);
    }

    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $limit 页数
     * @param Array $where 查询条件数组 默认空数组
     */
    public function list (Request $request) {
        $validator = Validator::make($request->all(), $this->ValidationRules(),$this->ValidationMes());
        if ($validator->fails()) {
            ReturnJson(FALSE,$validator->errors()->first());
        }
        $model = $this->ModelInstance()->query();
        if(!empty($request->where)){
            // 过滤条件数组，将空值的KEY过滤掉
            $where = array_filter($request->where,function($map){
                if($map != ''){
                    return true;
                }
            });
            // 使用Eloquent ORM来进行数据库查询
            foreach ($where as $field => $value) {
                // 如果值是数组，则使用whereIn方法
                if (is_array($value)) {
                    $model->whereIn($field, $value);
                } else {
                    $model->where($field, $value);
                }
            }
        }
        // 查询偏移量
        if(!empty($request->page) && !empty($request->limit)){
            $model->offset(($request->page - 1) * $request->limit);
        }
        // 查询条数
        if(!empty($request->limit)){
            $model->limit($request->limit);
        }
        // 数据排序
        $order = $request->order ? $request->order : 'id';
        $record = $model->orderBy($order)->get();
        ReturnJson(TRUE,'请求成功',$record);
    }
}