<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;

class PlateController extends CrudController
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
        ReturnJson(TRUE,'', $options);
    }
}
