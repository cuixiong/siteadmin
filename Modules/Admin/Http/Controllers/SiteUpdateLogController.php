<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Site;
use Modules\Admin\Http\Models\DictionaryValue;

class SiteUpdateLogController extends CrudController
{
    
    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            $data = [];
            // 语言
            $data['sites'] = (new Site())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);

            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $filed = ['english_name as label', 'value'];
            } else {
                $filed = ['name as label', 'value'];
            }
            $data['exec_status'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Exec_State', 'status' => 1]);


            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
