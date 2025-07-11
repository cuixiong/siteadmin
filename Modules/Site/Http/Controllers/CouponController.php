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
use Modules\Site\Http\Models\Publisher;
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
                $data['edition_value_list'][] = [
                    'value' => $item['id'],
                    'label' => $item['name']."-"
                               ."{$language_list[$item['language_id']]}(语言)"
                ];
            }
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
