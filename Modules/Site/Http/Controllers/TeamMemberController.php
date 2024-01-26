<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\ProductsCategory;

class TeamMemberController extends CrudController
{
    public function options(Request $request){
        $options = [];
        $codes = ['Switch_State','Is_Analyst'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
            }
        }
        $options['category'] = ProductsCategory::where('status',1)->select('id as value','name as label')->orderBy('sort','asc')->get()->toArray();
        ReturnJson(TRUE,'', $options);
    }
}
