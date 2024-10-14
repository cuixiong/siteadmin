<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\EmailLog;
use Modules\Admin\Http\Models\OperationLog;

class PublicController extends Controller {
    public function truncateTable(Request $request) {
        try {
            $type = $request->input('type' , '');
            if(empty($type )){
                ReturnJson(false, trans('lang.param_error'));
            }
            //站点端邮件日志，封禁日志，操作日志，同步日志加一个清空数据库的按钮
            if($type == 'email'){
                EmailLog::truncate();
            }elseif($type == 'operation'){
                OperationLog::truncate();
            }

            ReturnJson(true, trans('lang.request_success'), []);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }

    }

}
