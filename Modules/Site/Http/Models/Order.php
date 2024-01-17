<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Country;

class Order extends Base
{

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['is_pay_text', 'pay_time_format', 'invoice_time_format', 'pay_type_text', 'invoice_state_text','post_type_text','channel', 'country', 'province', 'city'];

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'order_number',     // 内部订单号
        'out_order_num',    // 外部订单号/第三方返回的订单号
        'is_pay',           // 支付状态
        'pay_time',         // 支付时间
        'pay_type',         // 支付方式
        'wechat_type',      // 微信支付所调用的场景
        'is_mobile_pay',    // 移动端设备支付
        'order_amount',     // 订单总价(原价，不打折，无优惠，日语不计算消费税)
        'actually_paid',    // 实付金额(用户实际支付价格，可能含打折、优惠券，日语包含消费税)
        'coupon_id',        // 优惠券id
        'status',           // 状态(目前在后台代表已读未读)
        'is_delete',        // 逻辑删除状态

        'user_id',      // 登录下单记录用户id
        'username',     // 用户名称
        'email',        // 邮箱
        'company',      // 企业名称
        'phone',        // 联系电话(带区号)
        'country_id',   // 用户信息-国家id
        'province_id',  // 用户信息-省份id
        'city_id',      // 用户信息-城市id
        'channel_id',      // 获知渠道
        'post_id',      // 物流方式: ems、顺丰等等
        'address',      // 邮寄地址

        'ip',           // 下单者ip
        'ip_region',    // 下单者ip位置
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

        // order_number
        if (isset($search->order_number) && !empty($search->order_number)) {
            $model = $model->where('order_number', 'like', '%' . $search->order_number . '%');
        }

        // out_order_num
        if (isset($search->out_order_num) && !empty($search->out_order_num)) {
            $model = $model->where('out_order_num', 'like', '%' . $search->out_order_num . '%');
        }

        // is_pay 
        if (isset($search->is_pay) && $search->is_pay != '') {
            $model = $model->where('is_pay', $search->is_pay);
        }
        // pay_type 
        if (isset($search->pay_type) && $search->pay_type != '') {
            $model = $model->where('pay_type', $search->pay_type);
        }

        // wechat_type 
        if (isset($search->wechat_type) && $search->wechat_type != '') {
            $model = $model->where('wechat_type', $search->wechat_type);
        }

        // is_mobile_pay 
        if (isset($search->is_mobile_pay) && $search->is_mobile_pay != '') {
            $model = $model->where('is_mobile_pay', $search->is_mobile_pay);
        }



        // order_amount 
        if (isset($search->order_amount) && $search->order_amount != '') {
            $model = $model->where('order_amount', $search->order_amount);
        }
        // actually_paid 
        if (isset($search->actually_paid) && $search->actually_paid != '') {
            $model = $model->where('actually_paid', $search->actually_paid);
        }
        // coupon_id 
        if (isset($search->coupon_id) && $search->coupon_id != '') {
            $model = $model->where('coupon_id', $search->coupon_id);
        }
        // status 
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }
        // is_delete 
        if (isset($search->is_delete) && $search->is_delete != '') {
            $model = $model->where('is_delete', $search->is_delete);
        }
        // user_id 
        if (isset($search->user_id) && $search->user_id != '') {
            
            if (is_numeric($search->user_id)) {
                $model = $model->where('user_id', $search->user_id);
            } else {
                $userIds = User::query()->select(['id'])->where('name', 'like', '%' . $search->user_id . '%')->where('status', 1)->pluck('id');
                $model = $model->whereIn('user_id', $userIds);
            }
        }

        // username
        if (isset($search->username) && !empty($search->username)) {
            $model = $model->where('username', 'like', '%' . $search->username . '%');
        }

        // email
        if (isset($search->email) && !empty($search->email)) {
            $model = $model->where('email', 'like', '%' . $search->email . '%');
        }

        // company
        if (isset($search->company) && !empty($search->company)) {
            $model = $model->where('company', 'like', '%' . $search->company . '%');
        }

        // phone
        if (isset($search->phone) && !empty($search->phone)) {
            $model = $model->where('phone', 'like', '%' . $search->phone . '%');
        }
        // country_id
        if (isset($search->country_id) && !empty($search->country_id)) {
            $model = $model->where('country_id', $search->country_id);
        }
        // province_id
        if (isset($search->province_id) && !empty($search->province_id)) {
            $model = $model->where('province_id', $search->province_id);
        }
        // city_id
        if (isset($search->city_id) && !empty($search->city_id)) {
            $model = $model->where('city_id', $search->city_id);
        }

        // channel_id
        if (isset($search->channel_id) && !empty($search->channel_id)) {
            $model = $model->where('channel_id', $search->channel_id);
        }

        // post_id
        if (isset($search->post_id) && !empty($search->post_id)) {
            $model = $model->where('post_id', $search->post_id);
        }

        // address
        if (isset($search->address) && !empty($search->address)) {
            $model = $model->where('address', 'like', '%' . $search->address . '%');
        }
        // ip
        if (isset($search->ip) && !empty($search->ip)) {
            $model = $model->where('ip', 'like', '%' . $search->ip . '%');
        }
        // ip_region
        if (isset($search->ip_region) && !empty($search->ip_region)) {
            $model = $model->where('ip_region', 'like', '%' . $search->ip_region . '%');
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

        // 支付时间
        if (isset($search->pay_time) && !empty($search->pay_time)) {
            $payTime = $search->pay_time;
            $model = $model->where('pay_time', '>=', $payTime[0]);
            $model = $model->where('pay_time', '<=', $payTime[1]);
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



    /**
     * 开票状态获取器
     */
    public function getInvoiceStateTextAttribute()
    {
        $text = '';
        if (isset($this->attributes['id'])) {
            $apply_status = Invoice::query()->select(['apply_status'])->where('order_id', $this->attributes['id'])->value('apply_status') ?? 0;

            $text = DictionaryValue::where('code', 'Invoice_State')->where('value', $apply_status)->value('name');
        }
        return $text ?? '';
    }
    
    /**
     * 获知渠道获取器
     */
    public function getChannelAttribute()
    {
        $text = '';
        if (isset($this->attributes['channel_id'])) {
            $text = DictionaryValue::where('code', 'Channel_Type')->where('value', $this->attributes['channel_id'])->value('name');
        }
        return $text ?? '';
    }

    /**
     * 邮寄方式获取器
     */
    public function getPostTypeTextAttribute()
    {
        $text = '';
        if (isset($this->attributes['post_id'])) {
            $text = DictionaryValue::where('code', 'Post_Type')->where('value', $this->attributes['post_id'])->value('name');
        }
        return $text ?? '';
    }

    /**
     * 国家获取器
     */
    public function getCountryAttribute()
    {
        return Country::getCountryName($this->attributes['country_id']);
    }

    /**
     * 省份获取器
     */
    public function getProvinceAttribute()
    {
        return Country::getCityName($this->attributes['province_id']);
    }

    /**
     * 城市获取器
     */
    public function getCityAttribute()
    {
        return Country::getCityName($this->attributes['city_id']);
    }
}
