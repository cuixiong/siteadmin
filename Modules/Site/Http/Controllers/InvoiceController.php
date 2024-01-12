<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;

class InvoiceController extends CrudController
{

    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $pageSize 页数
     * @param Array $where 查询条件数组 默认空数组
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
                $model = $model->orderBy('id', $sort)->orderBy('created_at', 'DESC');
            }

            $record = $model->get();

            $data = [
                'total' => $total,
                'list' => $record
            ];
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {

            if ($request->HeaderLanguage == 'en') {
                $filed = ['english_name as label', 'value'];
            } else {
                $filed = ['name as label', 'value'];
            }

            // 开票状态
            $data['status'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Invoice_State', 'status' => 1], ['sort' => 'ASC']);
            // 发票类型
            $data['invoice_type'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Invoice_Type', 'status' => 1], ['sort' => 'ASC']);
            

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    
    
}