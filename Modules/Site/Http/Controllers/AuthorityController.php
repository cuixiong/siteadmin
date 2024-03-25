<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\QuoteCategory;

class AuthorityController extends CrudController
{
    public function options(Request $request){
        $options = [];
        $codes = ['Switch_State','quote_cage'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
            }
        }
        $options['product_category'] = (new ProductsCategory())->GetListLabel(['id as value','name as label','pid','id'],true,'pid',['status' => 1]);
        $options['quote_category'] = (new QuoteCategory())->GetListLabel(['id as value','name as label'],false,'',['status' => 1]);
        ReturnJson(TRUE,'', $options);
    }
}
