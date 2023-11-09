<?php

namespace Modules\Admin\Http\Controllers;

use FFI;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Illuminate\Support\Facades\DB;

class DictionaryController extends CrudController
{

    /**
     * AJax单个更新
     * @param $request 请求信息
     */
    protected function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            DB::beginTransaction();
            $count = $this->ModelInstance()->where('code',$input['code'])->where('id','<>',$input['id'])->count();
            if($count > 0){
                DB::rollback();
                ReturnJson(FALSE,trans('lang.code_exists'));
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            if(!$record->update($input)){
                DB::rollback();
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            DictionaryValue::where('parent_id' ,$input['id'])->update(['code' => $input['code']]);
            DB::commit();
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            DB::rollback();
            ReturnJson(FALSE,$e->getMessage());
        }
    }
    /**
     * 删除字典
     * @param $ids 主键ID
     */
    public function destroy(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            DB::beginTransaction();
            $record = $this->ModelInstance()->query();
            $ids = $request->ids;
            if(!is_array($ids)){
                $ids = explode(",",$ids);
            }
            $record->whereIn('id',$ids);
            DictionaryValue::whereIn('parent_id',$ids)->delete();
            if(!$record->delete()){
                DB::rollBack();
                ReturnJson(FALSE,trans('lang.delete_error'));
            }
            DB::commit();
            ReturnJson(TRUE,trans('lang.delete_success'));
        } catch (\Exception $e) {
            DB::rollBack();
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
        $NameField = $request->Language == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->select('code','value',$NameField)->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        ReturnJson(TRUE,'', $options);
    }
}
