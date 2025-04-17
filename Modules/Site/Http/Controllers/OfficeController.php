<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Country;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;

class OfficeController extends CrudController
{
    public function options(Request $request){
        $options = [];
        $codes = ['Switch_State'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        $options['country'] = Country::where('status',1)->select('id as value',$NameField)->orderBy('sort','asc')->get()->toArray();
        ReturnJson(TRUE,'', $options);
    }

    public function changeViewStatus(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, trans('lang.param_empty').':id');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $field = $request->field ?? '';
            $value = $request->value ?? 0;
            if(!in_array($field ,  ['working_language_status', 'working_time_status', 'time_zone_status'])){
                ReturnJson(false, 'filed is error');
            }

            $record->$field = $value;

            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }



    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    protected function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $time_zone = $input['time_zone'] ?? '';
            if(empty($$time_zone )){
                $timezone = date_default_timezone_get();
            }
            $tz = new \DateTimeZone($timezone);
            // 当前时间的DateTime对象
            $now = new \DateTime('now', $tz);
            $input['time_zone_copy'] = $now->format('h:i a');

            $record = $this->ModelInstance()->create($input);
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
            $time_zone = $input['time_zone'] ?? '';
            if(empty($time_zone )){
                $time_zone = date_default_timezone_get();
            }
            $tz = new \DateTimeZone($time_zone);
            // 当前时间的DateTime对象
            $now = new \DateTime('now', $tz);
            $input['time_zone_copy'] = $now->format('h:i a');

            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


}
