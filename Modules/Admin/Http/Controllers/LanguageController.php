<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Language;
use Modules\Admin\Http\Models\DictionaryValue;

class LanguageController extends CrudController
{
    /**
     * 获取语言下拉框数据
     * 
     */
    public function getLanguage(Request $request)
    {
        $data = Language::select('id','name')->get()->toArray();

        ReturnJson(TRUE,trans('lang.request_success'),$data);
    }

    
    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            $data = [];
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $filed = ['english_name as label', 'value'];
            } else {
                $filed = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code'=>'Switch_State']);

            ReturnJson(TRUE, trans('lang.request_success'), $data );
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
