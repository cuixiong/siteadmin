<?php
namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CrudController extends Controller
{
    protected $model; // 模型类名:若没有指定模型，则根据控制器名找到对应的模型
    protected $action; // 请求方法名称
    protected $validate; // 请求方法名称
    public function __construct()
    {
        // 模型类名:若没有指定模型，则根据控制器名找到对应的模型
        if(empty($this->model)){
            $Controller = (new \ReflectionClass($this))->getShortName();// 控制器名
            $name = str_replace('Controller','',$Controller);
            $model = 'Modules\Admin\Http\Models\\'.$name;// Model(模型)
            $validate = 'Modules\Admin\Http\Requests\\'.$name.'Request';// Validate(数据验证)
            $this->model = $model;
            $this->validate = $validate;
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
    protected function ValidateInstance($request){
        $class = $this->validate;
        $validator = new $class();// 实例表单验证类
        $validator = $validator->DoVlidate($request);
    }

    /**
     * 单个新增
     * @param $request 请求信息
     */
    protected function store(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->create($input);
            if(!$record){
                ReturnJson(FALSE,'新增失败');
            }
            ReturnJson(TRUE,'新增成功',['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * AJax单行删除
     * @param $ids 主键ID
     */
    protected function destroy(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $record = $this->ModelInstance()->query();
            $ids = $request->ids;
            if(!is_array($ids)){
                $ids = explode(",",$ids);
            }
            $record->whereIn('id',$ids);
            if(!$record->delete()){
                ReturnJson(FALSE,'删除失败');
            }
            ReturnJson(TRUE,'删除成功');
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * AJax单个更新
     * @param $request 请求信息
     */
    protected function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $input['updated_by'] = $request->user->id;
            $record = $this->ModelInstance()->findOrFail($request->id);
            if(!$record->update($input)){
                ReturnJson(FALSE,'更新失败');
            }
            ReturnJson(TRUE,'更新成功');
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }



    /**
     * AJax单个查询
     * @param $request 请求信息
     */
    protected function form(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $record = $this->ModelInstance()->findOrFail($request->id);
            ReturnJson(TRUE,'请求成功',$record);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $pageSize 页数
     * @param Array $where 查询条件数组 默认空数组
     */
    protected function list (Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model,$request);
            // 总数量
            $total = $model->count();
            // 查询偏移量
            if(!empty($request->pageNum) && !empty($request->pageSize)){
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if(!empty($request->pageSize)){
                $model->limit($request->pageSize);
            }
            // 数据排序
            $order = $request->order ? $request->order : 'id';
            // 升序/降序
            $sort = (strtoupper($request->sort) == 'ASC') ? 'ASC' : 'DESC';
            $record = $model->select($ModelInstance->ListSelect)->orderBy($order,$sort)->get();
            
            $data = [
                'total' => $total,
                'list' => $record
            ];
            ReturnJson(TRUE,'请求成功',$data);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 查询value-label格式列表
     * @param $request 请求信息
     * @param Array $where 查询条件数组 默认空数组
     */
    public function option (Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $record = $ModelInstance->GetListLabel(['id as value','name as label']);
            ReturnJson(TRUE,'请求成功',$record);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}