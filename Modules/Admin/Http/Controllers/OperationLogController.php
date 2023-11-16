<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\OperationLog;

class OperationLogController extends CrudController
{
    public static function AddLog($modelName,$type,$content)
    {
        $request = request();
        $site = $request->header('Site');
        $category = $site ? 2 : 1;
        $name = $request->route()->getName();
        $route = request()->path();
        $model = new OperationLog();
        $model->type = $type;
        $model->category = $category;
        $model->route = $route;
        $model->title = $route;
        $model->content = $content;
        $model->site = $site;
        $model->save();
    }
}
