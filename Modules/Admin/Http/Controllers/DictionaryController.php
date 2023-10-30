<?php

namespace Modules\Admin\Http\Controllers;
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
            $record = $this->ModelInstance()->findOrFail($request->id);
            if(!$record->update($input)){
                DB::rollback();
                ReturnJson(FALSE,'更新失败');
            }
            $res = DictionaryValue::where('parent_id' ,$input['id'])->update(['code' => $input['code']]);
            if(!$res){
                DB::rollback();
                ReturnJson(FALSE,'更新失败');
            }
            DB::commit();
            ReturnJson(TRUE,'更新成功');
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
                ReturnJson(FALSE,'删除失败');
            }
            DB::commit();
            ReturnJson(TRUE,'删除成功');
        } catch (\Exception $e) {
            DB::rollBack();
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
