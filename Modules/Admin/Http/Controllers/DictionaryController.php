<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Illuminate\Support\Facades\DB;

class DictionaryController extends CrudController
{
    /**
     * 删除字典
     * @param $ids 主键ID
     */
    public function store(Request $request)
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
