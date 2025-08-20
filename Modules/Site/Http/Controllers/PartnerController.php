<?php

namespace Modules\Site\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\FaqCategory;

class PartnerController extends CrudController{
    public function searchDroplist(Request $request) {
        try {
            $data = [];
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State','status' => 1], ['sort' => 'ASC']);

            $data['type'] = [
                ['label' => '全部' , 'value' => 0],
                ['label' => '首页' , 'value' => 1],
                ['label' => '其他' , 'value' => 2]
            ];

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

}
