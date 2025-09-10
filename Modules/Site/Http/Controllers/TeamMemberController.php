<?php

namespace Modules\Site\Http\Controllers;

use DateTimeZone;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\ProductsCategory;

class TeamMemberController extends CrudController {
    public function options(Request $request) {
        $options = [];
        $codes = ['Switch_State', 'Is_Analyst'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select('code', 'value', $NameField)
                               ->orderBy('sort', 'asc')->get()->toArray();
        if (!empty($data)) {
            foreach ($data as $map) {
                $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
            }
        }
        $options['category'] = ProductsCategory::where('status', 1)->select('id as value', 'name as label')->orderBy(
            'sort', 'asc'
        )->get()->toArray();
        //时区下拉
        $time_zone_list = [
            'Asia/Shanghai',
            'Asia/Tokyo',
            'Asia/Dubai',
            'Europe/London',
            'Europe/Paris',
            'Europe/Moscow',
            'America/New_York',
            'America/Los_Angeles',
            'America/Chicago',
        ];
        foreach ($time_zone_list as $for_time_zone){
            $time_zone_select_list[] = [
                'label' => $for_time_zone,
                'value' => $for_time_zone,
            ];
        }
        $options['time_zone_list'] = $time_zone_select_list;
            ReturnJson(true, '', $options);
    }

    // 修改分析师状态
    public function ChangeAnalyst(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->is_analyst = $request->analyst;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    // 修改分析师状态
    public function changeShowProduct(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->show_product = $request->show_product ?? 1;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
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
            //$input['is_analyst'] = $input['is_analyst'] ?? 0;
            if (empty($input['language'])) {
                $input['language'] = '';
            }
            if (empty($input['phone'])) {
                $input['phone'] = '';
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
