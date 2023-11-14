<?php
namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\SystemValue;

// 系统设置
class SystemController extends CrudController
{
    /**
     * syytem的Value值保存
     * @param use Illuminate\Http\Request;
     * @return Json bool
     */
    public function systemValueList(Request $request) {
        try {
            $ModelInstance = new SystemValue();
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
            ReturnJson(TRUE,trans('lang.request_success'),$data);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
    /**
     * syytem的Value值保存
     * @param use Illuminate\Http\Request;
     * @return Json bool
     */
    public function systemValueStore(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = SystemValue::create($input);
            if(!$record){
                ReturnJson(FALSE,trans('lang.add_error'));
            }
            ReturnJson(TRUE,trans('lang.add_success'),['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * syytem的Value值更新
     * @param use Illuminate\Http\Request;
     * @return Json bool
     */
    public function systemValueUpdate(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = SystemValue::findOrFail($request->id);
            if(!$record->update($input)){
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * syytem的Value值删除
     * @param $ids 主键ID
     */
    public function systemValueDestroy(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $record = SystemValue::query();
            $ids = $request->ids;
            if(!is_array($ids)){
                $ids = explode(",",$ids);
            }
            $record->whereIn('id',$ids);
            if(!$record->delete()){
                ReturnJson(FALSE,trans('lang.delete_error'));
            }
            ReturnJson(TRUE,trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * select value one data
     * @param use Illuminate\Http\Request;
     */
    public function formValue(Request $request)
    {
        try {
            $record = SystemValue::findOrFail($request->id);
            ReturnJson(TRUE,trans('lang.request_success'),$record);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * Query child list based on parent ID
     * @param use Illuminate\Http\Request;
     */
    public function valueList(Request $request)
    {
        try {
            $list = SystemValue::where('parent_id',$request->id)->where('status',1)->get();
            ReturnJson(TRUE,trans('lang.request_success'),$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
