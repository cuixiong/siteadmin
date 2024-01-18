<?php

namespace Modules\Site\Http\Models;

use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Base;
class Applyfor extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'email', 'company', 'country','channel','status','message_id','product_id','category_id','updated_by', 'created_by'];
    protected $appends = ['product_name','message_name','category_id','channel_name'];

    // 产品名称获取器
    public function getProductNameAttribute()
    {
        if(isset($this->Attribute['product_id'])){
            $value = Products::where('id',$this->Attribute['product_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 类型名称获取器
    public function getMessageNameAttribute()
    {
        if(isset($this->Attribute['message_id'])){
            $value = MessageCategory::where('id',$this->Attribute['message_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 分类名称获取器
    public function getCategoryIdAttribute()
    {
        if(isset($this->Attribute['category_id'])){
            $value = ProductsCategory::where('id',$this->Attribute['category_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 来源名称获取器
    public function getChannelNameAttribute()
    {
        if(isset($this->Attribute['channel'])){
            $value = DictionaryValue::GetNameAsCode('Channel_Type',$this->Attribute['channel']);
        } else {
            $value = "";
        }
        return $value;
    }
}