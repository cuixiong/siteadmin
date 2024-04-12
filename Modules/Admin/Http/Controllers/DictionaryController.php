<?php

namespace Modules\Admin\Http\Controllers;

use FFI;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\Dictionary;

class DictionaryController extends CrudController
{
    // 全量更新
    protected function test(Request $request)
    {
        return Dictionary::SaveToSite(Dictionary::SAVE_TYPE_FULL, null, true);
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
            // 同步到分站点
            Dictionary::SaveToSite(Dictionary::SAVE_TYPE_SINGLE, $record->id, true);

            ReturnJson(TRUE, trans('lang.add_success'), ['id' => $record->id]);
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
            DB::beginTransaction();
            $count = $this->ModelInstance()->where('code', $input['code'])->where('id', '<>', $input['id'])->count();
            if ($count > 0) {
                DB::rollback();
                ReturnJson(FALSE, trans('lang.code_exists'));
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                DB::rollback();
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            DictionaryValue::where('parent_id', $input['id'])->update(['code' => $input['code']]);
            DB::commit();
            // 同步到分站点
            Dictionary::SaveToSite(Dictionary::SAVE_TYPE_SINGLE, $input['id'], true);
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            DB::rollback();
            ReturnJson(FALSE, $e->getMessage());
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
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            $record->whereIn('id', $ids);
            DictionaryValue::whereIn('parent_id', $ids)->delete();
            if (!$record->delete()) {
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.delete_error'));
            }
            DB::commit();
            // 同步到分站点
            foreach ($ids as $id) {
                Dictionary::SaveToSite(Dictionary::SAVE_TYPE_SINGLE, $id, true);
            }
            ReturnJson(TRUE, trans('lang.delete_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
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
        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select('code', 'value', $NameField)->orderBy('sort', 'asc')->get()->toArray();
        if (!empty($data)) {
            foreach ($data as $map) {
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        ReturnJson(TRUE, '', $options);
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
            $childIds = DictionaryValue::where('parent_id', $request->id)->pluck('id')->toArray();
            if ($childIds) {
                foreach ($childIds as $key => $value) {
                    $model = DictionaryValue::find($value);
                    $model->status = $request->status;
                    $model->save();
                }
            }
            Dictionary::SaveToSite(Dictionary::SAVE_TYPE_SINGLE, $request->id, true);
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

}
