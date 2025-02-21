<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\FaqCategory;
use Modules\Site\Http\Models\PostSubjectLog;

class PostSubjectLogController extends CrudController
{

    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    protected function list(Request $request)
    {
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
            $record = $model->get()?->toArray() ?? [];
            foreach ($record as $key => $item) {
                $record[$key]['details'] = !empty($item['details']) ? explode("\n", $item['details']) : [];
                $record[$key]['type_name'] = PostSubjectLog::getLogTypeList()[$record[$key]['type']] ?? '';
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
            
            $createrIds = PostSubjectLog::query()->distinct()->select('created_by')->pluck('created_by');
            $data['created_by'] = $createrIds;
        
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
