<?php

namespace Modules\Site\Http\Controllers;

use App\Const\PayConst;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\Pay;

class CurrencyConfigController extends CrudController
{

    public function searchDroplist(Request $request)
    {
        try {
            $data = [];
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);

            $data['is_first'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'V_Show', 'status' => 1], ['sort' => 'ASC']);

            $data['is_show'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'V_Show', 'status' => 1], ['sort' => 'ASC']);

            $data['currency_code'] = array_map(function ($item) {
                return  ['label' => $item, 'value' => $item];
            }, PayConst::$coinTypeALL);

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    protected function store(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                ReturnJson(false, trans('lang.add_error'));
            }

            //同步支付方式的汇率以及税率
            $update = [];
            $update['pay_exchange_rate'] =  $record->exchange_rate;
            $update['pay_tax_rate'] = $record->tax_rate;
            Pay::query()->where('pay_coin_type', $record->code)->update($update);

            ReturnJson(true, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


    /**
     * AJax单个更新
     *
     * @param $request 请求信息
     */
    protected function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }

            //同步支付方式的汇率以及税率
            $update = [];
            $update['pay_exchange_rate'] =  $record->exchange_rate;
            $update['pay_tax_rate'] = $record->tax_rate;
            Pay::query()->where('pay_coin_type', $record->code)->update($update);

            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 修改主货币
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeFirst(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->is_first = $request->is_first;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


    /**
     * 修改是否展示
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeShowHome(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->is_show = $request->is_show;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
