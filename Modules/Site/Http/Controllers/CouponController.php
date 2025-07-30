<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Language;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Coupon;
use Modules\Site\Http\Models\CouponUser;
use Modules\Site\Http\Models\Pay;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\OrderGoods;
use Modules\Site\Http\Models\PriceEdition;
use Modules\Site\Http\Models\PriceEditionValue;
use Modules\Admin\Http\Models\Publisher;
use Modules\Site\Http\Models\User;

class CouponController extends CrudController {
    /**
     * 获取搜索下拉列表
     *
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request) {
        try {
            //用户名单
            $data['users'] = (new User())->GetListLabel(['id as value', 'username as label'], false, '', ['status' => 1]
            );
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            // 是否生效
            $data['is_effect'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Coupon_State', 'status' => 1], ['sort' => 'ASC']
            );
            // 优惠类型
            $data['type'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Coupon_Type', 'status' => 1], ['sort' => 'ASC']
            );
            //当前站点的出版商
            $site = getSiteName();
            $publisher_id_list = Site::query()->where('name', $site)->value('publisher_id');
            $publisher_id_list = explode(',', $publisher_id_list);
            $publisher_list = Publisher::query()->whereIn('id', $publisher_id_list)->get()->toArray();
            foreach ($publisher_list as $item) {
                $data['publishers'][] = [
                    'value' => $item['id'],
                    'label' => $item['name']
                ];
            }
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

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
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $record = $model->get()->toArray();
            $price_values_list = $this->getPriceListByDB();
            $handler_price_values_list = [];
            foreach ($price_values_list as $price_valies_info) {
                $handler_price_values_list[$price_valies_info['value']] = $price_valies_info['label'];
            }
            foreach ($record as &$item) {
                $price_edition_values = $item['price_edition_values'] ?? '';
                $price_edition_value_list = explode(',', $price_edition_values);
                if (empty($price_edition_value_list)) {
                    $item['price_values_list'] = [];
                } else {
                    $for_price_values_list = [];
                    foreach ($price_edition_value_list as $for_price_edition_value_id) {
                        $for_price_values_list[] = $handler_price_values_list[$for_price_edition_value_id] ?? '';
                    }
                    $item['price_values_list'] = $for_price_values_list;
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

    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    protected function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                ReturnJson(false, trans('lang.add_error'));
            } else {
                $user_ids = $input['user_ids'];
                if (!empty($user_ids)) {
                    $user_ids = explode(',', $user_ids);
                    foreach ($user_ids as $user_id) {
                        CouponUser::create([
                                               'user_id'    => $user_id,
                                               'coupon_id'  => $record->id,
                                               'is_used'    => 0,
                                               'created_at' => time(),
                                           ]);
                    }
                }
            }
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
    protected function update(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            } else {
                $user_ids = $input['user_ids'];
                if (!empty($user_ids)) {
                    $userIds = explode(',', $user_ids);
                    $existUserIds = CouponUser::query()->select('user_id')->where(['coupon_id' => $record->id])->pluck(
                        'user_id'
                    )->toArray();
                    //对比后，新增和删除
                    $addIds = array_values(array_diff($userIds, $existUserIds));
                    $deletedIds = array_values(array_diff($existUserIds, $userIds));
                }
                if (!empty($addIds)) {
                    foreach ($addIds as $addId) {
                        if (empty($addId)) {
                            continue;
                        }
                        CouponUser::create([
                                               'user_id'    => $addId,
                                               'coupon_id'  => $record->id,
                                               'is_used'    => 0,
                                               'created_at' => time(),
                                           ]);
                    }
                }
                if (!empty($deletedIds)) {
                    CouponUser::query()->whereIn('user_id', $deletedIds)->where(
                        ['coupon_id' => $record->id]
                    )->delete();
                } else if (empty($user_ids)) {
                    //用户id为空, 删除所有
                    CouponUser::query()->where(['coupon_id' => $record->id])->delete();
                }
            }
            ReturnJson(true, trans('lang.update_success'));
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
            $orderRecord = Coupon::query()->whereIn('id', $ids);
            if (!$orderRecord->delete()) {
                ReturnJson(false, trans('lang.delete_error'));
            } else {
                $orderGoodsRecord = CouponUser::query()->whereIn('coupon_id', $ids);
                $orderGoodsRecord->delete();
            }
            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function getEditionValues(Request $request) {
        try {
            $publisher_id = $request->publisher_id ?? 0;
            $data['edition_value_list'] = $this->getPriceListByDB($publisher_id);
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     *
     *
     * @return array
     */
    private function getPriceListByDB($publisher_id = 0) {
        $language_list = Language::query()->where("status", 1)->pluck("name", 'id')->toArray();
        $publisher_list = Publisher::query()->where("status", 1)->pluck("name", 'id')->toArray();
        if (empty($publisher_id)) {
            //当前站点的出版商
            $site = getSiteName();
            $publisher_id_list = Site::query()->where('name', $site)->value('publisher_id');
            $publisher_id_list = explode(',', $publisher_id_list);
        } else {
            $publisher_id_list = [$publisher_id];
        }
        $PriceEdition_list = PriceEdition::query()->where("status", 1)
                                         ->where("is_deleted", 1)
                                         ->get()->toArray();
        $edition_list = [];
        foreach ($PriceEdition_list as $key => $item) {
            $for_publisher_id_list = explode(',', $item['publisher_id']);
            foreach ($publisher_id_list as $forpublisher_id) {
                if (in_array($forpublisher_id, $for_publisher_id_list)) {
                    $cnt = PriceEditionValue::query()
                                            ->where('edition_id', $item['id'])
                                            ->where("status", 1)
                                            ->where("is_deleted", 1)
                                            ->count();
                    if ($cnt > 0) {
                        $edition_list[$item['id']] = $publisher_list[$forpublisher_id] ?? '';
                    }
                }
            }
        }
        $edition_id_list = array_keys($edition_list);
        $PriceEditionValueList = PriceEditionValue::query()->whereIn('edition_id', $edition_id_list)
                                                  ->where("status", 1)
                                                  ->where("is_deleted", 1)
                                                  ->get()->toArray();
        $data = [];
        foreach ($PriceEditionValueList as $item) {
            $for_publisher_name = '';
            //需求明确 : 站点只有一个版本的就不需要显示出版商了
            if(count($publisher_id_list) > 1){
                //取交集
                $EditionPublisherIds = PriceEdition::query()->where("id" , $item['edition_id'])->value("publisher_id");
                $EditionPublisherIdList = explode(',', $EditionPublisherIds);
                $intersect_publisher_id_list = array_intersect($EditionPublisherIdList, $publisher_id_list);
                foreach ($intersect_publisher_id_list as $temp_for_publisher_id){
                    $for_publisher_name .= "({$publisher_list[$temp_for_publisher_id]})";
                }
            }
            $data[] = [
                'value' => $item['id'],
                'label' => $item['name']."-".$for_publisher_name
                           ."{$language_list[$item['language_id']]}(语言)"
            ];
        }

        return $data;
    }
}
