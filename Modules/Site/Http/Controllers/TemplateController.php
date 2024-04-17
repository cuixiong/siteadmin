<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;

class TemplateController extends CrudController {
    /**
     * 获取搜索下拉列表
     *
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request) {
        try {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            //模版分类列表
            $model = new TemplateCategory();
            $temp_cate_list = $model->GetListLabel(['id as value', 'name as label'], false, '',
                                                   ['status' => 1]);
            $data['temp_cate_list'] = $temp_cate_list;
            // 颜色列表
            $data['show_home'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'template_color', 'status' => 1], ['sort' => 'ASC']
            );
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    public function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $cate_ids = $input['cate_ids'];
            $cate_id_list = explode(",", $cate_ids);
            $modelInstance = $this->ModelInstance();
            $record = $modelInstance->create($input);
            //先移除后添加
            $record->tempCates()->detach();
            $record->tempCates()->attach($cate_id_list);
            if (!$record) {
                ReturnJson(false, trans('lang.add_error'));
            }
            ReturnJson(true, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单个更新
     *
     * @param $request 请求信息
     */
    protected function update(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            //维护中间表
            $cate_ids = $input['cate_ids'];
            $cate_id_list = explode(",", $cate_ids);
            $record->tempCates()->detach();
            $record->tempCates()->attach($cate_id_list);
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 删除
     *
     * @param $ids 主键ID
     */
    protected function destroy(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            foreach ($ids as $id) {
                $record = $this->ModelInstance()->find($id);
                if ($record) {
                    $res = $record->delete();
                    if($res > 0){
                        $record->tempCates()->detach();
                    }
                }
            }
            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
