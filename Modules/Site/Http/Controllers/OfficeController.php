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
                ReturnJson(false, 'id is empty');
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

}
