<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\OperationLog;

class OperationLogController extends CrudController
{
    public static function AddLog($type)
    {
        $request = request();
        $site = $request->header('Site');
        $category = $site ? 2 : 1;
        $route = $request->route()->getName();
        $model = new OperationLog();
        $model->type = $type;
        $model->category = $category;
        $model->route = $route;
        $model->title = $route;
        $model->save();
    }
}
