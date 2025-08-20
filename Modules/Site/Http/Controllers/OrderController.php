<?php

namespace Modules\Site\Http\Controllers;

use App\Const\OrderConst;
use App\Const\PayConst;
use App\Const\QueueConst;
use App\Jobs\ExportJob;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\City;
use Modules\Admin\Http\Models\Country;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Invoice;
use Modules\Site\Http\Models\Language;
use Modules\Site\Http\Models\OrderExportLog;
use Modules\Site\Http\Models\Pay;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\OrderGoods;
use Modules\Site\Http\Models\PriceEditionValue;
use Modules\Site\Http\Models\Products;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Models\ViewProductsExportLog;
use Modules\Site\Http\Models\ViewProductsLog;

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

            $paymentData = Pay::query()->get()->toArray();
            $paymentData = array_column($paymentData, null, 'code');

            foreach ($record as $key => &$value) {
                if (!empty($value['send_email_time'])) {
                    $value['send_email_time_str'] = date('Y-m-d H:i:s', $value['send_email_time']);
                } else {
                    $value['send_email_time_str'] = '';
                }
                if ($value['is_mobile_pay'] == 1) {
                    $value['is_mobile_pay_text'] = '移动端';
                } else {
                    $value['is_mobile_pay_text'] = 'PC端';
                }
                $value['order_goods_list'] = $orderModel->getOrderProductInfo($value['id']);
                $value['pay_coin_type_str'] = PayConst::$coinTypeSymbol[$value['pay_coin_type']] ?? '';

                $payInfo = $paymentData[$value['pay_code']];
                $exchange_rate = 1;
                if (!empty($payInfo)) {
                    $exchange_rate = $payInfo['pay_exchange_rate'];
                    if ($exchange_rate <= 0) {
                        $exchange_rate = 1;
                    }
                }

                // 根据支付方式决定货币单位，根据支付状态决定实时汇率或者订单记录的当时汇率
                if ($value['is_pay'] == 1) {
                    // 未付款返回该支付方式的实时汇率
                    $value['exchange_rate'] = $exchange_rate;
                } elseif ($value['is_pay'] == 2) {
                    // 已支付读取订单记录的汇率
                    $exchange_rate = $value['exchange_rate'];
                }

                $value['exchange_coupon_amount'] = bcmul($value['coupon_amount'], $exchange_rate, 2);
                $value['exchange_order_amount'] = bcmul($value['order_amount'], $exchange_rate, 2);
                $value['exchange_actually_paid'] = bcmul($value['actually_paid'], $exchange_rate, 2);

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

            // 根据支付方式决定货币单位，根据支付状态决定实时汇率或者订单记录的当时汇率
            if($record['is_pay'] == 1){
                // 未付款返回该支付方式的实时汇率
                $record['exchange_rate'] = $exchange_rate;
            }elseif($record['is_pay'] == 2){
                // 已支付读取订单记录的汇率
                $exchange_rate = $record['exchange_rate'];
            }

            $record['exchange_coupon_amount'] = bcmul($record['coupon_amount'], $exchange_rate, 2);
            $record['exchange_order_amount'] = bcmul($record['order_amount'], $exchange_rate, 2);
            $record['exchange_actually_paid'] = bcmul($record['actually_paid'], $exchange_rate, 2);

            $record['order_goods_list'] = (new Order())->getOrderProductInfo($request->id);
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 批量导出
     *
     * @param $request 请求信息
     */
    public function export(Request $request) {
        try {
            list($model, $count) = $this->getExportData($request);
            //返回条数
            if ($request->type == 1) {
                ReturnJson(true, trans('lang.request_success'), ['count' => $count]);
            }
            //定义导出目录
            $basePath = public_path();
            $site = getSiteName();
            if (empty($site)) {
                ReturnJson(false, trans('lang.site_not_exist'));
            }
            $dirMiddlePath = '/site/'.$site.'/exportDir/';
            $dirPath = $basePath.$dirMiddlePath;
            if (!is_dir($dirPath)) {
                @mkdir($dirPath, 0777, true);
            }
            //定义导出文件名
            $dirName = "order_export_".time();
            $filePath = $dirMiddlePath.$dirName.'.xlsx';
            //生成导出日志
            $addLog = [
                'file'  => $filePath,
                'count' => $count,
            ];
            $logModel = OrderExportLog::create($addLog);
            $isQueue = false;
            if ($isQueue) {
                $data = [
                    'class'    => 'Modules\Site\Http\Controllers\OrderController',
                    'method'   => 'handleExportExcel',
                    'site'     => $site,   //站点名称
                    'reqinput' => $request->input(),    //model 实例
                    'log_id'   => $logModel->id,  //写入日志的id
                ];
                $data = json_encode($data);
                ExportJob::dispatch($data)->onQueue(QueueConst::QUEEU_EXPORT_VIEW_GOODS);
            } else {
                $data = [
                    'class'    => 'Modules\Site\Http\Controllers\OrderController',
                    'method'   => 'handleExportExcel',
                    'site'     => $site,   //站点名称
                    'reqinput' => $request->input(),    //model 实例
                    'log_id'   => $logModel->id,  //写入日志的id
                ];
                $this->handleExportExcel($data);;
            }
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
        ReturnJson(true, trans('lang.request_success'), $logModel->id);
    }

    /**
     *
     * @param 请求信息|Request $request
     *
     * @return array
     */
    private function getExportData(Request $request) {
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $ModelInstance = $this->ModelInstance();
        $model = $ModelInstance->query();
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty').':ids');
            }
            $model = $ModelInstance->whereIn('id', $ids);
        } else {
            //筛选
            $model = $ModelInstance->HandleWhere($model, $request);
        }
        $count = $model->count();
        if (empty($count)) {
            ReturnJson(true, trans('lang.data_empty'));
        }

        return [$model, $count];
    }

    public function handleExportExcel($params) {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        if (empty($params['site'])) {
            throw new \Exception("site is empty", 1);
        }
        // 设置当前租户
        tenancy()->initialize($params['site']);
        request()->headers->set('Site', $params['site']);
        $exportLogInfo = OrderExportLog::find($params['log_id']);
        $reqinput = $params['reqinput'];
        try {
            //读取数据
            $model = new Order();
            if (!empty($reqinput['ids'])) {
                $idList = explode(",", $reqinput['ids']);
                $model = $model->whereIn('id', $idList);
            } elseif (!empty($reqinput['search'])) {
                $model = $model->HandleSearch($model, $reqinput['search']);
            }
            $record = $model->get()->toArray();
            $writer = WriterEntityFactory::createXLSXWriter();
            $filename = public_path().$exportLogInfo['file'];
            if (!file_exists($filename)) {
                file_put_contents($filename, '');
            }
            $writer->openToFile($filename);
            $style = (new StyleBuilder())->setShouldWrapText(false)->build();
            //写入标题
            $title = [
                '订单号',
                '用户',
                '邮箱',
                '手机号',
                '公司',
                '国家',
                '地址',
                '报告名称',
                '价格',
                '语言',
                '版本',
                '数量',
                '付款状态',
                '付款时间',
                '订单金额',
                '折扣金额',
                '实付金额',
                '是否开票',
                '支付方式',
                '备注',
                '创建时间',
                '修改时间',
                //'状态',
            ];
            if (request()->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $row = WriterEntityFactory::createRowFromArray($title, $style);
            $writer->addRow($row);
            $payList = Pay::query()->get()->keyBy('code')->toArray();
            $languageList = Language::query()->pluck('name' ,'id')->toArray();
            foreach ($record as $key => $item) {
                $real_address = '';
                if (!empty($item['province_id'])) {
                    $real_address .= City::query()->where('id', $item['province_id'])->value('name');
                }
                if (!empty($item['city_id'])) {
                    $real_address .= City::query()->where('id', $item['city_id'])->value('name');
                }
                $real_address .= $item['address'];
                $exchange_rate = 1;
                if (!empty($payList[$item['pay_code']])) {
                    $exchange_rate = $payList[$item['pay_code']]['pay_exchange_rate'];
                    if ($exchange_rate <= 0) {
                        $exchange_rate = 1;
                    }
                }
                $apply_status = Invoice::query()->select(['apply_status'])->where('order_id', $item['id'])
                                       ->value('apply_status') ?? 0;
                $Invoicetext = DictionaryValue::where('code', 'Invoice_State')->where('value', $apply_status)->value(
                    'name'
                );
                if (empty($Invoicetext)) {
                    $Invoicetext = '未开票';
                }
                //$Symbol = PayConst::$coinTypeSymbol[$item['pay_coin_type']] ?? '';
                $Symbol = $item['pay_coin_type'];
                $exchange_coupon_amount = bcmul($item['coupon_amount'], $exchange_rate, 2);
                $exchange_order_amount = bcmul($item['order_amount'], $exchange_rate, 2);
                $order_list = OrderGoods::query()->where("order_id", $item['id'])->get()->toArray();

                $product_name = '';
                $product_price = '';
                $product_language = '';
                $product_edition = '';
                $product_quantity = '';
                foreach ($order_list as $order_goods){
                    $product_name .= Products::query()->where('id', $order_goods['goods_id'])->value("name")."\n";
                    $product_price .= $order_goods['goods_original_price']."\n";
                    $PriceEditionValueInfo = PriceEditionValue::query()->where('id', $order_goods['price_edition'])->first();
                    if(!empty($PriceEditionValueInfo )){
                        $product_language .= $languageList[$PriceEditionValueInfo['language_id']]."\n";
                        $product_edition .= $PriceEditionValueInfo['name']."\n";
                    }else{
                        $product_language .= '--'."\n";
                        $product_edition .= '--'."\n";
                    }
                    $product_quantity .= $order_goods['goods_number']."\n";
                }
                $row = [];
                $row[] = $item['order_number'];
                $row[] = $item['username'];
                $row[] = $item['email'];
                $row[] = $item['phone'];
                $row[] = $item['company'];
                $row[] = Country::getCountryName($item['country_id']) ?? '';
                $row[] = $real_address;
                $row[] = $product_name;
                $row[] = $product_price;
                $row[] = $product_language;
                $row[] = $product_edition;
                $row[] = $product_quantity;
                $row[] = OrderConst::$PAY_STATUS_TYPE[$item['is_pay']];
                if(empty($item['pay_time'] )){
                    $row[] = '';
                }else {
                    $row[] = date('Y-m-d H:i:s', $item['pay_time']);
                }
                $row[] = $Symbol.$exchange_order_amount;
                $row[] = $Symbol.$exchange_coupon_amount;
                $row[] = $Symbol.$item['actually_paid'];
                $row[] = $Invoicetext;
                $row[] = $payList[$item['pay_code']]['name'] ?? '';
                $row[] = $item['remarks'];
                $row[] = $item['created_at'];
                $row[] = $item['updated_at'];
                $rowFromValues = WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }
            // $writer->addRows($record);
            $writer->close();
        } catch (\Exception $th) {
            $details = $th->getMessage();
            throw $th;
        }
        //记录任务状态
        $logModel = OrderExportLog::where(['id' => $params['log_id']])->first();
        $logData = [
            'state' => OrderExportLog::EXPORT_COMPLETE,
        ];
        $logData['success_count'] = count($record);
        $logModel->update($logData);

        return true;
    }
}
