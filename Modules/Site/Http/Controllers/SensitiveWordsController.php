<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;

class SensitiveWordsController extends CrudController {
    public function searchDroplist(Request $request) {
        try {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
