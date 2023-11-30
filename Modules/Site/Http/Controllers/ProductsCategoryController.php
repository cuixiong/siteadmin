<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Admin\Http\Models\DictionaryValue;

class ProductsCategoryController extends CrudController
{
    
    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $filed = ['english_name as label', 'value'];
            } else {
                $filed = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Switch_State', 'status' => 1]);


            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
