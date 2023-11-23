<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\Department;
use Modules\Admin\Http\Models\DictionaryValue;

class DepartmentController extends CrudController
{
    /**
     * 查询列表页
     * @param $request 请求信息
     * @param Array $where 查询条件数组 默认空数组
     */
    public function list (Request $request) {
        try {
            $search = $request->input('search');
            $filed = ['id','parent_id','name','status','sort','created_at as createTime','updated_at as updateTime'];
            $list = (new Department)->GetList($filed,true,'parent_id',$search);
            ReturnJson(TRUE,trans('lang.request_success'),$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
    /**
     * 递归树状value-label格式
     */
    public function option (Request $request) {
        try {
            $list = (new Department)->GetList(['id','id as value','parent_id','name as label'],true,'parent_id',['status' => 1]);
            ReturnJson(TRUE,trans('lang.request_success'),$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * get dict options
     * @return Array
     */
    public function options(Request $request)
    {
        $options = [];
        $codes = ['Switch_State'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        ReturnJson(TRUE,'', $options);
    }

    public function changeStatus(Request $request)
    {
        try {
            if(empty($request->id)){
                ReturnJson(FALSE,'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->status = $request->status;
            if(!$record->save()){
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            $childerIds = Department::TreeGetChildIds($request->id);
            $this->ModelInstance()->whereIn('id',$childerIds)->update(['status' => $request->status]);
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            $childerIds = Department::TreeGetChildIds($request->id);
            $this->ModelInstance()->whereIn('id',$childerIds)->update(['status' => $request->status]);
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

}
