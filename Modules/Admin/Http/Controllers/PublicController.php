<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Dictionary;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\EmailLog;
use Modules\Admin\Http\Models\OperationLog;

class PublicController extends Controller {
    public function truncateTable(Request $request) {
        try {
            $type = $request->input('type', '');
            if (empty($type)) {
                ReturnJson(false, trans('lang.param_error'));
            }
            //站点端邮件日志，封禁日志，操作日志，同步日志加一个清空数据库的按钮
            if ($type == 'email') {
                EmailLog::truncate();
            } elseif ($type == 'operation') {
                OperationLog::truncate();
            }
            ReturnJson(true, trans('lang.request_success'), []);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function getPageSetting(Request $request) {
        try {
            $dict_value_model = new DictionaryValue();
            $key = 'pagesize_setting';
            $dict_value_list = $dict_value_model->where("code", $key)
                                                ->where("status", 1)
                                                ->orderBy("sort", "asc")
                                                ->pluck('value')->toArray();
            $data['page_size_setting'] = $dict_value_list;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
