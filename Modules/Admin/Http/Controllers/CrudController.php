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
        if (empty($this->model)) {
            $Controller = (new \ReflectionClass($this))->getShortName(); // 控制器名
            $name = str_replace('Controller', '', $Controller);
            $model = 'Modules\Admin\Http\Models\\' . $name; // Model(模型)
            $validate = 'Modules\Admin\Http\Requests\\' . $name . 'Request'; // Validate(数据验证)
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
    protected function ValidateInstance($request)
    {
        $class = $this->validate;
        $validator = new $class(); // 实例表单验证类
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
            if (!$record) {
                ReturnJson(FALSE, trans('lang.add_error'));
            }
            ReturnJson(TRUE, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
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
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            foreach ($ids as $id) {
                $record = $this->ModelInstance()->find($id);
                if($record){
                    $record->delete();
                }
            }
            ReturnJson(TRUE, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
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
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
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
            ReturnJson(TRUE, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $pageSize 页数
     * @param Array $where 查询条件数组 默认空数组
     */
    protected function list(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            // 总数量
            $total = $model->count();
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }

            $record = $model->get();

            $data = [
                'total' => $total,
                'list' => $record
            ];
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 查询value-label格式列表
     * @param $request 请求信息
     * @param Array $where 查询条件数组 默认空数组
     */
    public function option(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $record = $ModelInstance->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);
            ReturnJson(TRUE, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 修改状态
     * @param $request 请求信息
     * @param $id 主键ID
     */
    public function changeStatus(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(FALSE, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->status = $request->status;
            if (!$record->save()) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 修改排序
     * @param $request 请求信息
     * @param $id 主键ID
     */
    public function changeSort(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(FALSE, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->sort = $request->sort;
            if (!$record->save()) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 为用户设置自定义表头
     * @param Request $request 请求信息
     * @param int $id 主键ID
     */
    public function setHeaderTitle(Request $request)
    {
        $titleJson = $request->input('title_json');
        $userId = $request->user->id;
        try {
            if (empty($userId)) {
                ReturnJson(FALSE, trans('lang.param_empty') . ':user_id');
            } elseif (empty($titleJson)) {
                ReturnJson(FALSE, trans('lang.param_empty') . ':title_json');
            }

            $ModelInstance = $this->ModelInstance();
            $data = (new \Modules\Admin\Http\Models\ListStyle())->setHeaderTitle(class_basename($ModelInstance::class), $userId, $titleJson);
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
