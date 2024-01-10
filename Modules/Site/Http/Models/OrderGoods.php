<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class OrderGoods extends Base
{

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'order_id',             // 订单id
        'goods_id',             // 商品id
        'goods_number',         // 商品购买数量
        'goods_original_price', // 商品原价(单价)
        'goods_present_price',  // 商品现价(单价)
        'price_edition',        // 商品版本价格
        
    ];

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request)
    {

        $search = json_decode($request->input('search'));
        //id 
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }
        // order_id 
        if (isset($search->order_id) && $search->order_id != '') {
            $model = $model->where('order_id', $search->order_id);
        }
        // goods_id 
        if (isset($search->goods_id) && $search->goods_id != '') {
            $model = $model->where('goods_id', $search->goods_id);
        }
        // goods_number 
        if (isset($search->goods_number) && $search->goods_number != '') {
            $model = $model->where('goods_number', $search->goods_number);
        }

        // goods_original_price 
        if (isset($search->goods_original_price) && $search->goods_original_price != '') {
            $model = $model->where('goods_original_price', $search->goods_original_price);
        }
        // goods_present_price 
        if (isset($search->goods_present_price) && $search->goods_present_price != '') {
            $model = $model->where('goods_present_price', $search->goods_present_price);
        }
        // price_edition 
        if (isset($search->price_edition) && $search->price_edition != '') {
            $model = $model->where('price_edition', $search->price_edition);
        }

        // 时间为数组形式
        // 创建时间
        if (isset($search->created_at) && !empty($search->created_at)) {
            $createTime = $search->created_at;
            $model = $model->where('created_at', '>=', $createTime[0]);
            $model = $model->where('created_at', '<=', $createTime[1]);
        }

        // 更新时间
        if (isset($search->updated_at) && !empty($search->updated_at)) {
            $updateTime = $search->updated_at;
            $model = $model->where('updated_at', '>=', $updateTime[0]);
            $model = $model->where('updated_at', '<=', $updateTime[1]);
        }


        return $model;
    }




}
