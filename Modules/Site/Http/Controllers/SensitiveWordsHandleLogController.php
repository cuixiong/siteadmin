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

    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    protected function list(Request $request) {
        
        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", '1024M'); 
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            // 总数量
            $total = $model->count();
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $record = $model->get();
            if($record){
                $record = $record->toArray();
                $logTypeList = SensitiveWordsHandleLog::getLogTypeList();
                foreach ($record as $key => $item) {
                    $record[$key]['log_type_name'] = $logTypeList[$item['log_type']]??'';
                }
            }

            $data = [
                'total' => $total,
                'list'  => $record
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

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
