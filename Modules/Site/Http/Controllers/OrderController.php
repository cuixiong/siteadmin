<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Pay;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\OrderGoods;
use Modules\Site\Http\Models\Products;
use Modules\Admin\Http\Models\User;

class OrderController extends CrudController
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
            $fieldsList = ['id', 'order_number', 'out_order_num', 'user_id', 'is_pay', 'pay_time', 'pay_code', 'order_amount', 'actually_paid', 'status', 'username', 'email', 'created_at'];
            $model = $model->select($fieldsList);
            // 数据排序. 默认降序
            if(empty($request->sort )){
                $request->sort = 'desc';
            }
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('id', $sort);
            }

            $record = $model->get()->toArray();
            $orderModel = new Order();
            foreach ($record as $key => &$value){
                $value['order_goods_list'] = $orderModel->getOrderProductInfo($value['id']);
            }

            $data = [
                'total' => $total,
                'list' => $record,
                'headerTitle' => [],
            ];
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
            ReturnJson(FALSE, $e->getTraceAsString());
        }
    }

    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {

            //支付方式
            // $data['pay_type'] = (new Pay())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);
            $data['pay_type'] = (new Pay())->GetListLabel(['code as value', 'name as label'], false, '', ['status' => 1]);

            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }



            // 支付状态
            $data['pay_status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Pay_State', 'status' => 1], ['sort' => 'ASC']);

            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);


            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * AJax单行删除
     * @param $ids 主键ID
     */
    protected function destroy(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }

            foreach ($ids as $id) {
                $record = $this->ModelInstance()->find($id);
                if ($record) {
                    $record->delete();
                    $orderGoodsRecord = OrderGoods::query()->whereIn('order_id', $ids);
                    $orderGoodsRecord->delete();
                }
            }
            ReturnJson(TRUE, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 重写单查接口
     * @param Request $request
     *
     */
    protected function form(Request $request) {
        try {
            $this->ValidateInstance($request);
            $modelInstance = $this->ModelInstance();
            $record = $modelInstance->findOrFail($request->id);

            if(!empty($modelInstance->formAppends )){
                foreach ($modelInstance->formAppends as $forField){
                    $record->$forField = $record->$forField;
                }
            }
            $record['order_goods_list'] = (new Order())->getOrderProductInfo($request->id);

            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

}
