<?php

namespace Modules\Site\Http\Controllers;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;

class ContactUsController extends CrudController
{
    public function options(Request $request){
        $options = [];
        $codes = ['Switch_State','Channel_Type','Buy_Time'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        ReturnJson(TRUE,'', $options);
    }
}
