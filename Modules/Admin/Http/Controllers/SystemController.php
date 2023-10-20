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
    public function systemValueStore(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $input['created_by'] = $request->user->id;
            $record = SystemValue::create($input);
            if(!$record){
                ReturnJson(FALSE,'新增失败');
            }
            ReturnJson(TRUE,'新增成功',['id' => $record->id]);
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
            $input['updated_by'] = $request->user->id;
            $record = SystemValue::findOrFail($request->id);
            if(!$record->update($input)){
                ReturnJson(FALSE,'更新失败');
            }
            ReturnJson(TRUE,'更新成功');
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
                ReturnJson(FALSE,'删除失败');
            }
            ReturnJson(TRUE,'删除成功');
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
