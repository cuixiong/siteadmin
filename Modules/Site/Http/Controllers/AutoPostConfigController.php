<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\AutoPostConfig;
use Modules\Site\Http\Models\NewsCategory;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;

class AutoPostConfigController extends CrudController {
    public function searchDroplist(Request $request) {
        try {
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            $data['title_temp_list'] = (new Template)->GetListLabel(['id as value', 'name as label'], false, '',
                                                                    ['status' => 1, 'type' => 2]);
            $data['conent_temp_list'] = (new Template)->GetListLabel(['id as value', 'name as label'], false, '',
                                                                     ['status' => 1, 'type' => 1]);
            //报告分类
            $data['category'] = (new ProductsCategory())->GetList(['id as value', 'name as label', 'id', 'pid'], false,'', ['status' => 1]);

            // 站内站外
            $data['type'] = [];
            $typeList = AutoPostConfig::getSiteTypeList();
            foreach ($typeList as $key => $value) {
                $data['type'][] = ['label' => $value, 'value' => $key];
            }
            // 站内新闻类型
            $data['news_category'] = (new NewsCategory())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);

            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
