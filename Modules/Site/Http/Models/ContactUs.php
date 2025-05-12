<?php

namespace Modules\Site\Http\Models;

use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Base;

class ContactUs extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'category_id',
        'product_id',
        'name',
        'email',
        'product_name', 
        'country_id',
        'province_id',
        'city_id',
        'phone',
        'company',
        'department',
        'address',
        'channel',
        'status',
        'buy_time',
        'content',
        'price_edition',
        'language_version',
        'sort',
        'updated_by',
        'created_by',
    ];
    protected $appends = ['message_name', 'category_name', 'category_style', 'channel_name', 'buy_time_name'];

    // 产品名称获取器
    public function getProductNameAttribute()
    {
        if (isset($this->attributes['product_id']) && !empty($this->attributes['product_id'])) {
            $value = Products::where('id', $this->attributes['product_id'])->value('name');

        } else {
            if (isset($this->attributes['product_name'])) {
                $value = $this->attributes['product_name'];
            }else{
                $value = "";
            }
        }
        return $value;
    }

    // 类型名称获取器
    public function getMessageNameAttribute()
    {
        if (isset($this->attributes['message_id'])) {
            $value = MessageCategory::where('id', $this->attributes['message_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 分类名称获取器
    public function getCategoryNameAttribute()
    {
        if (isset($this->attributes['category_id'])) {
            $value = MessageCategory::where('id', $this->attributes['category_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 分类样式获取器
    public function getCategoryStyleAttribute()
    {
        if (isset($this->attributes['category_id'])) {
            $value = MessageCategory::where('id', $this->attributes['category_id'])->value('style');
        } else {
            $value = "";
        }
        return !empty($value) ? $value : '';
    }

    // 来源名称获取器
    public function getChannelNameAttribute()
    {
        if (isset($this->attributes['channel'])) {
            $value = DictionaryValue::GetNameAsCode('Channel_Type', $this->attributes['channel']);
        } else {
            $value = "";
        }
        return $value;
    }

    // 购买时间获取器
    public function getBuyTimeNameAttribute()
    {
        if (isset($this->attributes['buy_time'])) {
            $value = $this->attributes['buy_time'] . '天内';
            return $value;
        }
        return '';
    }
}
