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

    /**
     * 修改分类折扣
     * @param $request 请求信息
     * @param $id 主键ID
     */
    public function discount(Request $request)
    {

        try {
            if (empty($request->id)) {
                ReturnJson(FALSE, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);

            $record->discount = $request->discount ?? 100;
            $record->discount_amount = $request->discount_amount ?? 0;
            $record->discount_time_begin = $request->discount_time_begin;
            $record->discount_time_end = $request->discount_time_end;
            if (!$record->save()) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
