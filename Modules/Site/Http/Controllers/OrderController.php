<?php

namespace Modules\Site\Http\Controllers;

use App\Const\OrderConst;
use App\Const\PayConst;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\City;
use Modules\Admin\Http\Models\Country;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Pay;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\OrderGoods;
use Modules\Site\Http\Models\Products;
use Modules\Admin\Http\Models\User;

class OrderController extends CrudController {
    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    protected function list(Request $request) {
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
            $fieldsList = ['id', 'order_number', 'out_order_num', 'user_id', 'is_pay', 'pay_time', 'pay_code',
                           'order_amount', 'actually_paid', 'status', 'username', 'email', 'created_at',
                           'pay_coin_type', 'send_email_time' , 'is_mobile_pay'];
            $model = $model->select($fieldsList);
            // 数据排序. 默认降序
            if (empty($request->sort)) {
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
            foreach ($record as $key => &$value) {
                if(!empty($value['send_email_time'] )){
                    $value['send_email_time_str'] = date('Y-m-d H:i:s', $value['send_email_time']);
                }else{
                    $value['send_email_time_str'] = '';
                }
                if($value['is_mobile_pay'] == 1){
                    $value['is_mobile_pay_text'] = 'PC端';
                }else{
                    $value['is_mobile_pay_text'] = '移动端';
                }


                $value['order_goods_list'] = $orderModel->getOrderProductInfo($value['id']);
                $value['pay_coin_type_str'] = PayConst::$coinTypeSymbol[$value['pay_coin_type']] ?? '';
            }
            $data = [
                'total'       => $total,
                'list'        => $record,
                'headerTitle' => [],
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
            ReturnJson(false, $e->getTraceAsString());
        }
    }

    /**
     * 获取搜索下拉列表
     *
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request) {
        try {
            //支付方式
            // $data['pay_type'] = (new Pay())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);
            $data['pay_type'] = (new Pay())->GetListLabel(['code as value', 'name as label'], false, '', ['status' => 1]
            );
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            // 支付状态
            $paudStatus = [];
            foreach (OrderConst::$PAY_STATUS_TYPE as $payKey => $payStatus) {
                $add_data = [];
                $add_data['label'] = $payStatus;
                $add_data['value'] = $payKey;
                $paudStatus[] = $add_data;
            }
            $data['pay_status'] = $paudStatus;
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单行删除
     *
     * @param $ids 主键ID
     */
    protected function destroy(Request $request) {
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
            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 重写单查接口
     *
     * @param Request $request
     *
     */
    protected function form(Request $request) {
        try {
            $this->ValidateInstance($request);
            $modelInstance = $this->ModelInstance();
            $record = $modelInstance->findOrFail($request->id);
            if (!empty($modelInstance->formAppends)) {
                foreach ($modelInstance->formAppends as $forField) {
                    $record->$forField = $record->$forField;
                }
            }
            $record['pay_coin_type_str'] = PayConst::$coinTypeSymbol[$record['pay_coin_type']] ?? '';
            //订单地址信息
            $record['country_str'] = '';
            if (!empty($record['country_id'])) {
                $record['country_str'] = Country::getCountryName($record['country_id']);
            }
            $record['province_str'] = '';
            if (!empty($record['province_id'])) {
                $record['province_str'] = City::query()->where('id', $record['province_id'])->value('name');
            }
            $record['city_id_str'] = '';
            if (!empty($record['city_id'])) {
                $record['city_id_str'] = City::query()->where('id', $record['city_id'])->value('name');
            }
            $payInfo = Pay::where('code', $record['pay_code'])->first();
            $exchange_rate = 1;
            if (!empty($payInfo)) {
                $exchange_rate = $payInfo->pay_exchange_rate;
                if ($exchange_rate <= 0) {
                    $exchange_rate = 1;
                }
            }
            $record['exchange_coupon_amount'] = bcmul($record['coupon_amount'], $exchange_rate, 2);
            $record['exchange_order_amount'] = bcmul($record['order_amount'], $exchange_rate, 2);
            $record['order_goods_list'] = (new Order())->getOrderProductInfo($request->id);
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
