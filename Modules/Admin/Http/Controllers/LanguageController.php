<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Language;

class LanguageController extends CrudController
{
    public function getLanguage(Request $request)
    {
        $data = Language::select('id','language as name')->get()->toArray();

        ReturnJson(TRUE,'请求成功',$data);
    }
}
