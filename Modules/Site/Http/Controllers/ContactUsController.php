<?php

namespace Modules\Site\Http\Controllers;

use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\City;
use Modules\Admin\Http\Models\Country;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\MessageCategory;
use Modules\Site\Http\Models\MessageLanguageVersion;

class ContactUsController extends CrudController
{
    public function options(Request $request)
    {
        $options = [];
        $codes = ['Switch_State', 'Channel_Type', 'Buy_Time'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select('code', 'value', $NameField)->orderBy('sort', 'asc')->get()->toArray();
        if (!empty($data)) {
            foreach ($data as $map) {
                $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
            }
        }
        $options['categorys'] = (new MessageCategory)->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);

        $options['language_version'] = (new MessageLanguageVersion())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);

        $options['country'] = Country::where('status', 1)->select('id as value', 'name as label')->orderBy('sort', 'asc')->get()->toArray();


        $provinces = City::where(['status' => 1, 'type' => 1])->select('id as value', 'name as label')->orderBy('id', 'asc')->get()->toArray();

        foreach ($provinces as $key => $province) {
            $cities = City::where(['status' => 1, 'type' => 2, 'pid' => $province['value']])->select('id as value', 'name as label')->orderBy('id', 'asc')->get()->toArray();
            $provinces[$key]['children'] = $cities;
        }
        $options['city'] = $provinces;

        ReturnJson(TRUE, '', $options);
    }
}
