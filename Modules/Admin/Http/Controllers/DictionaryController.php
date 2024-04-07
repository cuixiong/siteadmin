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
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 更新全部的价格版本到Redis中
     */
    public function ToRedis(Request $request)
    {
        try {
            $list = Dictionary::select('id', 'code')->where('status', 1)->get()->toArray();

            // $count = Dictionary::count();
            // $i = 0;
            if ($list && count($list) > 0) {

                foreach ($list as $item) {

                    $option = DictionaryValue::select('id', 'name', 'value', 'status')
                        ->where(['status' => 1, 'parent_id' => $item['id']])
                        ->orderBy('sort', 'asc')
                        ->get()->toArray();

                    if ($option && count($option) > 0 && !empty($item['code'])) {
                        foreach ($option as $optionItem) {

                            $res = Dictionary::UpdateToRedis('dictionary_' . $item['code'], $optionItem);
                            // ReturnJson(FALSE, $res);
                        }
                        // if($res == true){
                        //     $i = $i + 1;
                        // }
                    }
                }
            }
            // echo '已成功同步：'.$i .' 总数量:'.$count;
            // exit;
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
}
