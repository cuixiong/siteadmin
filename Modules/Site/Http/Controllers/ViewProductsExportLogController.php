<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Models\ViewProductsExportLog;

class ViewProductsExportLogController extends CrudController {
    /**
     * 导出进度
     *
     * @param $request 请求信息
     */
    public function exportProcess(Request $request) {
        $logId = $request->id;
        if (empty($logId)) {
            ReturnJson(true, trans('lang.param_empty'));
        }
        $logData = ViewProductsExportLog::where('id', $logId)->first();
        if ($logData) {
            $logData = $logData->toArray();
        } else {
            ReturnJson(true, trans('lang.data_empty'));
        }
        $data = [
            'result' => true,
            'msg'    => '',
            'file'   => $logData['file'],
        ];
        $text = '';
        $updateTime = 0;
        if ($logData['state'] != ViewProductsExportLog::UPLOAD_COMPLETE) {
            $data['result'] = false;
        }
        $updatedTimestamp = strtotime($logData['updated_at']);
        if ($updatedTimestamp > $updateTime) {
            $updateTime = $updatedTimestamp;
        }
        switch ($logData['state']) {
            case ViewProductsExportLog::EXPORT_INIT:
                $text = trans('lang.export_init_msg');
                break;
            case ViewProductsExportLog::EXPORT_RUNNING:
                $text = trans('lang.export_running_msg').($logData['success_count'] + $logData['error_count']).'/'
                        .$logData['count'];
                break;
            case ViewProductsExportLog::EXPORT_MERGING:
                $text = trans('lang.export_merging_msg');
                break;
            case ViewProductsExportLog::EXPORT_COMPLETE:
                $text = trans('lang.export_complete_msg');
                break;
            default:
                # code...
                break;
        }
        $data['msg'] = $text;
        //五分钟没反应则提示
        if (time() > $updateTime + 86400) {
            $data = [
                'result' => false,
                'msg'    => trans('lang.time_out'),
            ];
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 下载导出文件
     *
     * @param $request 请求信息
     */
    public function exportFileDownload(Request $request) {
        $logId = $request->id;
        if (empty($logId)) {
            ReturnJson(true, trans('lang.param_empty'));
        }
        $logData = ViewProductsExportLog::where('id', $logId)->first();
        if ($logData) {
            $logData = $logData->toArray();
        } else {
            ReturnJson(true, trans('lang.data_empty'));
        }
        if ($logData['state'] == ViewProductsExportLog::EXPORT_COMPLETE) {
            $basePath = public_path();
            if (strpos($logData['file'], 'txt') !== false) {
                return response()->download($basePath.$logData['file'], null, [
                    'Content-Type'        => 'text/plain',
                    'Content-Disposition' => 'inline',
                ]);
            } elseif (strpos($logData['file'], 'xlsx') !== false) {
                return response()->download($basePath.$logData['file'], null, [
                    'Content-Type'        => 'application/vnd.ms-excel',
                    'Content-Disposition' => 'inline',
                ]);
            }
            ReturnJson(true, trans('lang.file_not_exist'));
        }else{
            ReturnJson(true, trans('lang.export_no_complete'));
        }

    }
}
