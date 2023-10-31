<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Country;

class CountryController extends CrudController
{
    public function getCountry(Request $request)
    {
        $data = Country::get()->toArray();
        $language = $request->input('language');
        $list = [];
        if(!empty($data)){
            foreach($data as $key=>$item){
                $name = json_decode($item['data']);
                if(!isset($name->$language)){
                    continue;
                }
                $list[$key]['id'] = $item['id'];
                $list[$key]['name'] = $name->$language;
            }
        }
        ReturnJson(TRUE,trans('lang.request_success'),$list);
    }
}
