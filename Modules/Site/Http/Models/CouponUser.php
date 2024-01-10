<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\DictionaryValue;

class CouponUser extends Base
{

    //将虚拟字段追加到数据对象列表里去
    protected $appends = [];

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'user_id',      // 内部订单号
        'coupon_id',    // 外部订单号/第三方返回的订单号
        'is_used',      // 支付状态
        'use_time',     // 支付时间
        'updated_by',   // 修改者
        'created_by',   // 创建者
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

        //coupon_id 
        if (isset($search->coupon_id) && !empty($search->coupon_id)) {
            $model = $model->where('coupon_id', $search->coupon_id);
        }

        // user_id 
        if (isset($search->user_id) && $search->user_id != '') {
            $model = $model->where('user_id', $search->user_id);
        }

        // is_used
        if (isset($search->is_used) && !empty($search->is_used)) {
            $model = $model->where('is_used', $search->is_used);
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
        
        // 使用时间
        if (isset($search->use_time) && !empty($search->use_time)) {
            $useTime = $search->use_time;
            $model = $model->where('use_time', '>=', $useTime[0]);
            $model = $model->where('use_time', '<=', $useTime[1]);
        }


        return $model;
    }

    /**
     * 支付状态获取器
     */
    public function getIsPayTextAttribute()
    {
        $text = '';
        if (isset($this->attributes['is_pay'])) {
            $name = DictionaryValue::where('code', 'Pay_State')->where('value', $this->attributes['is_pay'])->value('name');
            return $name ?? '';
        }
        return $text;
    }

    /**
     * 支付时间获取器
     */
    public function getPayTimeFormatAttribute()
    {
        $text = '';
        if (isset($this->attributes['pay_time']) && !empty($this->attributes['pay_time'])) {
            return date('Y-m-d H:i:s', $this->attributes['pay_time']);
        }
        return $text;
    }

    /**
     * 支付方式获取器
     */
    public function getPayTypeTextAttribute()
    {
        $text = '';
        if (isset($this->attributes['pay_type'])) {
            $name = Pay::query()->where('id', $this->attributes['pay_type'])->value('name');
            return $name ?? '';
        }
        return $text;
    }

    /**
     * 开票时间获取器
     */
    public function getInvoiceTimeFormatAttribute()
    {
        $text = '';
        if (isset($this->attributes['invoice_time']) && !empty($this->attributes['invoice_time'])) {
            return date('Y-m-d H:i:s', $this->attributes['invoice_time']);
        }
        return $text;
    }
}
