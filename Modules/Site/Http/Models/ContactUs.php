<?php

namespace Modules\Site\Http\Models;

use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Base;
class ContactUs extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'email', 'phone', 'company','channel','status','buy_time','updated_by', 'created_by'];
    protected $appends = ['product_name','message_name','category_id','channel_name'];

    // 产品名称获取器
    public function getProductNameAttribute()
    {
        if(isset($this->attributes['product_id'])){
            $value = Products::where('id',$this->attributes['product_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 类型名称获取器
    public function getMessageNameAttribute()
    {
        if(isset($this->attributes['message_id'])){
            $value = MessageCategory::where('id',$this->attributes['message_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 分类名称获取器
    public function getCategoryIdAttribute()
    {
        if(isset($this->attributes['category_id'])){
            $value = ProductsCategory::where('id',$this->attributes['category_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 来源名称获取器
    public function getChannelNameAttribute()
    {
        if(isset($this->attributes['channel'])){
            $value = DictionaryValue::GetNameAsCode('Channel_Type',$this->attributes['channel']);
        } else {
            $value = "";
        }
        return $value;
    }
}