<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Admin\Http\Models\PriceEdition as AdminPriceEdition;
use Modules\Admin\Http\Models\PriceEditionValue as AdminPriceEditionValue;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\Language;
use Modules\Site\Http\Models\PriceEdition;
use Modules\Site\Http\Models\PriceEditionValue;

class PriceEditionController extends CrudController {
    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    public function list(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            //增加假删除过滤条件
            $model = $model->where("is_deleted", 1);
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
            // 数据排序
            $order = $request->order ? $request->order : 'id';
            // 升序/降序
            $sort = (strtoupper($request->sort) == 'ASC') ? 'ASC' : 'DESC';
            $record = $model->select($ModelInstance->ListSelect)->orderBy($order, $sort)->get()->toArray();
            //查询后的数据处理
            if ($record && count($record) > 0) {
                foreach ($record as $key => $item) {
                    //子项数据
                    $record[$key]['items'] = PriceEditionValue::select(
                        'id', 'name', 'language_id', 'rules', 'notice', 'is_logistics', 'status', 'sort'
                    )
                                                              ->where('edition_id', $item['id'])
                                                              ->where("is_deleted", 1) //增加假删除逻辑
                                                              ->orderBy('sort', 'ASC')
                                                              ->get()->toArray();
                }
            }
            //表头排序
            $headerTitle = (new ListStyle())->getHeaderTitle(class_basename($ModelInstance::class), $request->user->id);
            $data = [
                'total'       => $total,
                'list'        => $record,
                'headerTitle' => $headerTitle ?? [],
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 获取搜索下拉列表
     *
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request) {
        try {
            $data = [];
            // 出版商
            $site = getSiteName();
            $publisher_ids = Site::query()->where('name', $site)->value('publisher_id');
            $publisher_id_list = explode(',', $publisher_ids);
            $data['publishers'] = Publisher::query()->whereIn('id', $publisher_id_list)
                                           ->where("status", 1)
                                           ->select('name as label', 'id as value')
                                           ->get()->toArray();
            // 语言
            $data['languages'] = (new Language())->GetListLabel(['id as value', 'name as label'], false, '',
                                                                ['status' => 1]);
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            //是否送货
            $data['logistics'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Logistics_State', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function syncAdminPrice() {
        //同步 price_editions
        $priceeditionList = AdminPriceEdition::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        foreach ($priceeditionList as $forPriceEdition) {
            $for_id = $forPriceEdition['id'];
            $priceEdition = PriceEdition::query()->where("id", $for_id)->first();
            if ($priceEdition) {
                // 存在则更新
                $priceEdition->save($forPriceEdition);
            } else {
                PriceEdition::insert($forPriceEdition);
            }
        }
        $priceValueList = AdminPriceEditionValue::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        foreach ($priceValueList as $forPriceValue) {
            $for_id = $forPriceValue['id'];
            $priceEditionValue = PriceEditionValue::query()->where("id", $for_id)->first();
            if ($priceEditionValue) {
                // 存在则更新
                $priceEditionValue->save($forPriceValue);
            } else {
                PriceEditionValue::insert($forPriceValue);
            }
        }
        ReturnJson(true, trans('lang.request_success'));
    }
}
