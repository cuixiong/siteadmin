<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\SensitiveWords;
use Modules\Site\Http\Models\SensitiveWordsHandleLog;
use Modules\Site\Services\SenWordsService;

class SensitiveWordsHandleLogController extends CrudController
{

    public function searchDroplist(Request $request)
    {
        try {
            
            $data = [];
            $data['log_type'] = [];
            $logType = SensitiveWordsHandleLog::getLogTypeList();
            foreach ($logType as $key => $value) {
                $data['type'][] = ['label' => $value, 'value' => $key];
            }

            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


}
