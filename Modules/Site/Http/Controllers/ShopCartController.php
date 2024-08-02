<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\PriceEditionValue;
use Modules\Site\Http\Models\PriceEditionValue as SitePriceEditionValue;
use Modules\Site\Http\Models\Language;
use Modules\Site\Http\Models\Pay;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\OrderGoods;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ShopCart;
use Modules\Site\Http\Models\User;

class ShopCartController extends CrudController
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
                $model = $model->orderBy('id', $sort);
            }

            $record = $model->get()->toArray();
            $productIdList = array_column($record, 'goods_id');
            $productList = Products::from('product_routine as p')->select(
                ['p.url', 'p.thumb', 'p.name', 'p.id', 'p.published_date', 'p.category_id', 'p.publisher_id', 'pc.thumb as category_thumb']
            )
                                   ->leftJoin('product_category as pc', 'pc.id', 'p.category_id')
                                   ->whereIn('p.id', $productIdList)
                                   ->get()->keyBy('id')->toArray();

            $languageList = Language::query()->where(['status' => 1])->pluck('name', 'id')->toArray();
            $doman = getSiteDomain();
            foreach ($record as $key => &$value){
                //语言版本
                $priceEditionId = $value['price_edition'];
                $priceEdition = SitePriceEditionValue::find($priceEditionId);
                if (!empty($priceEdition)) {
                    $language = isset($languageList[$priceEdition->language_id]) && !empty($languageList[$priceEdition->language_id]) ? $languageList[$priceEdition->language_id] :'';
                    $price_edition = $priceEdition['name'] ?: '';
                } else {
                    $language = '';
                    $price_edition = '';
                }
                $goods_id = $value['goods_id'];
                $productData = $productList[$goods_id];
                $productData['link'] = $this->getProductUrl($productData, $doman);
                $productData['language'] = $language;
                $productData['price_edition'] = $price_edition;
                $value['product_data'] = $productData;

            }

            $data = [
                'total' => $total,
                'list' => $record
            ];
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    public function getProductUrl($products, $domain = '') {
        if(empty($domain )) {
            $domain = getSiteDomain();
        }
        return $domain."/reports/{$products['id']}/{$products['url']}";
    }


    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        //价格版本
        $priceEditionIds = ShopCart::query()->select(['price_edition'])->distinct()->pluck('price_edition');
        $data['price_edition'] = PriceEditionValue::query()
            ->select(['id as value', 'name as label'])
            ->where(['status' => 1])
            ->whereIn('id', $priceEditionIds ?? [])
            ->get()
            ->makeHidden((new PriceEditionValue())->getAppends());

        //用户
        $userIds = ShopCart::query()->select(['user_id'])->distinct()->pluck('user_id');
        $data['user_id'] = User::query()
            ->select(['id as value', 'name as label'])
            ->where(['status' => 1])
            ->whereIn('id', $userIds ?? [])
            ->get()
            ->makeHidden((new User())->getAppends());

        try {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);


            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
