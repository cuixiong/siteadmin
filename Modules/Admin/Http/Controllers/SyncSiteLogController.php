<?php

namespace Modules\Admin\Http\Controllers;

use App\Const\NotityTypeConst;
use App\Jobs\NotifySite;
use Modules\Admin\Http\Models\DictionaryValue;

class SyncSiteLogController extends CrudController {
    public function searchDroplist() {
        // 状态开关
        $status_list[] = ['value' => 1, 'label' => '成功'];
        $status_list[] = ['value' => 0, 'label' => '失败'];
        $data['status'] = $status_list;


        foreach (NotityTypeConst::$typeMap as $key => $value){
            $data['type'][] = ['value' => $key, 'label' => $value];
        }

        ReturnJson(true, trans('lang.request_success'), $data);
    }
}
