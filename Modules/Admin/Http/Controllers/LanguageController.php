<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Language;

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
}
