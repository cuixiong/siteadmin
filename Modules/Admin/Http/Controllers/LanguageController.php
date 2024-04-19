<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Language;
use Modules\Admin\Http\Models\DictionaryValue;

class LanguageController extends CrudController
{
    // 全量更新
    protected function test(Request $request)
    {
        return Language::SaveToSite(Language::SAVE_TYPE_FULL, null, true);
    }

    /**
     * 获取语言下拉框数据
     * 
     */
    public function getLanguage(Request $request)
    {
        $data = Language::select('id','name')->get()->toArray();

        ReturnJson(TRUE,trans('lang.request_success'),$data);
    }

    
    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            $data = [];
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code'=>'Switch_State','status' => 1], ['sort'=>'ASC']);

            ReturnJson(TRUE, trans('lang.request_success'), $data );
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
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
            Language::SaveToSite(Language::SAVE_TYPE_SINGLE, $record->id, true);
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
                    Language::SaveToSite(Language::SAVE_TYPE_SINGLE, $id, true);
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
            // 同步到分站点
            Language::SaveToSite(Language::SAVE_TYPE_SINGLE, $record->id, true);
            ReturnJson(TRUE, trans('lang.update_success'));
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
            // 同步到分站点
            Language::SaveToSite(Language::SAVE_TYPE_SINGLE, $record->id, true);
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
            // 同步到分站点
            Language::SaveToSite(Language::SAVE_TYPE_SINGLE, $record->id, true);
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

}
