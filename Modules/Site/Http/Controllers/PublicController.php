<?php

namespace Modules\Site\Http\Controllers;

use App\Helper\SiteUploads;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Site\Http\Models\ContactUs;
use Modules\Site\Http\Models\EmailLog;
use Modules\Site\Http\Models\IpBanLog;
use Modules\Site\Http\Models\OperationLog;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\RequestLog;
use Modules\Site\Http\Models\SyncLog;

class PublicController extends Controller {
    public function getNoReadMsgCnt() {
        try {
            $data = [];
            $data['orderViewCnt'] = Order::query()->where('status', 0)->count();
            $data['contactUsViewCnt'] = ContactUs::query()->where('status', 0)->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function getInitData() {
        try {
            $data = [];
            $data['oss_base_path'] = str_replace(public_path(), '', SiteUploads::GetRootPath());
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

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
            }elseif($type == 'ip_ban'){
                IpBanLog::truncate();
            }elseif($type == 'sync_log'){
                SyncLog::truncate();
            }elseif('ua_ban'){
                RequestLog::truncate();
            }

            ReturnJson(true, trans('lang.request_success'), []);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }

    }

}
