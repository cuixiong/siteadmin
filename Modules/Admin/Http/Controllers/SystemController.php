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
            if($request->id){
                $model->where('parent_id',$request->id);
            }
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
            $model =  $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'ASC') ? 'ASC' : 'DESC';
            if(!empty($request->order)){
                $model = $model->orderBy($request->order,$sort);
            } else {
                $model = $model->orderBy('sort',$sort)->orderBy('created_at',$sort);
            }
            $record = $model->get();
            
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
     * Child modification status
     * @param use Illuminate\Http\Request;
     */
    public function valueChangeStatus(Request $request)
    {
        try {
            if(empty($request->id)){
                ReturnJson(FALSE,'id is empty');
            }
            $record = SystemValue::findOrFail($request->id);
            $record->status = $request->status;
            if(!$record->save()){
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            ReturnJson(TRUE,trans('lang.update_success'));
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
            $fileds = $request->HeaderLangague == 'en' ? ['id as value','english_name as label'] : ['id as value','name as label'];
            $record = $ModelInstance->GetListLabel($fileds,false,'',['status' => 1]);
            ReturnJson(TRUE,trans('lang.request_success'),$record);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
    /**
     * Query all children through parent
     * @param use Illuminate\Http\Request;
     */
    public function valueList (Request $request) {
        try {
            $record = (new SystemValue)->where('hidden',1)->where('parent_id',$request->parent_id)->get();
            ReturnJson(TRUE,trans('lang.request_success'),$record);
            
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * Child modification status
     * @param use Illuminate\Http\Request;
     */
    public function valueChangeHidden(Request $request)
    {
        try {
            if(empty($request->id)){
                ReturnJson(FALSE,'id is empty');
            }
            $record = SystemValue::findOrFail($request->id);
            $record->hidden = $request->hidden;
            if(!$record->save()){
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
