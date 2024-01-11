<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\PriceEdition;
use Modules\Admin\Http\Models\PriceEditionValue;

class ShopCart extends Base
{

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['goods_name', 'price_edition_name'];

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'user_id',     // 用户id
        'goods_id',    // 商品id
        'number',      // 加入的数量
        'status',       // 状态
        'price_edition',    // 选择的版本
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

        // status 
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }

        // user_id 
        if (isset($search->user_id) && $search->user_id != '') {
            $model = $model->where('user_id', $search->user_id);
        }

        // goods_id
        if (isset($search->goods_id) && !empty($search->goods_id)) {
            $model = $model->where('goods_id', $search->goods_id);
        }
        // number
        if (isset($search->number) && !empty($search->number)) {
            $model = $model->where('number', $search->number);
        }
        // price_edition
        if (isset($search->price_edition) && !empty($search->price_edition)) {
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


    /**
     * 报告名称获取器
     */
    public function getGoodsNameAttribute()
    {
        $text = '';
        if (isset($this->attributes['goods_id'])) {
            $name = Products::query()->where('id', $this->attributes['goods_id'])->value('name');
            return $name ?? '';
        }
        return $text;
    }

    /**
     * 价格版本获取器
     */
    public function getPriceEditionNameAttribute()
    {
        $text = '';
        if (isset($this->attributes['goods_id'])  && isset($this->attributes['price_edition'])) {

            // $publisherId = Products::query()->where('id', $this->attributes['goods_id'])->value('publisher_id');
            // $priceEditionId = PriceEdition::query()->whereRaw('FIND_IN_SET(?, publisher_id)', $publisherId)->value('id');
            $name = PriceEditionValue::query()->where('id', $this->attributes['price_edition'])->value('name');
            return $name ?? '';
        }
        return $text;
    }


    /**
     * 用户名获取器
     */
    public function getUsernameAttribute()
    {
        $text = '';
        if (isset($this->attributes['user_id'])) {
            $text = User::query()->where('id', $this->attributes['user_id'])->value('name');
        }
        return $text;
    }
}
