<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\FaqCategory;
use Modules\Site\Http\Models\PostSubjectLog;

class PostSubjectLogController extends CrudController
{
    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            $data = [];
            // // 状态开关
            // if ($request->HeaderLanguage == 'en') {
            //     $field = ['english_name as label', 'value'];
            // } else {
            //     $field = ['name as label', 'value'];
            // }
            // $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State','status' => 1], ['sort' => 'ASC']);
            $data['type'] = [];
            $logType = PostSubjectLog::getLogTypeList();
            foreach ($logType as $key => $value) {
                $data['type'][] = ['label' => $value, 'value' => $key];
            }

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
    
    /**
     * 删除
     * @param Request $request
     */
    public function destory(Request $request)
    {
        try {
            if (empty($request->ids)) {
                ReturnJson(FALSE, '请输入需要删除的ID');
            }
            DB::beginTransaction();
            $record = $this->ModelInstance()->query();
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            
            $rs = $record->whereIn('id', $ids)->delete();
            if (!$rs) {
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.delete_error'));
            }

            DB::commit();
            ReturnJson(TRUE, trans('lang.delete_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
