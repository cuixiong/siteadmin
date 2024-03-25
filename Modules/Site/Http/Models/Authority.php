<?php

namespace Modules\Site\Http\Models;

use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Base;
class Authority extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name', 
        'body',
        'link',
        'class_id',
        'keyword',
        'description',
        'big_image',
        'thumbnail',
        'status',
        'sort',
        'updated_by',
        'created_by',
        'category_id'
    ];

    protected $appends = [
        'class_name',
        'category_name',
    ];
    public function setBigImageAttribute($value)
    {
        $value = $value && is_array($value) ? implode(',',$value) : '';
        $this->attributes['big_image'] = $value;
        return $value;
    }

    public function getBigImageAttribute($value)
    {
        $value = $value  ? explode(',',$value) : [];
        return $value;
    }

    public function setThumbnailAttribute($value)
    {
        $value = $value && is_array($value) ? implode(',',$value) : '';
        $this->attributes['thumbnail'] = $value;
        return $value;
    }

    public function getThumbnailAttribute($value)
    {
        $value = $value  ? explode(',',$value) : [];
        return $value;
    }

    public function getClassNameAttribute($value)
    {
        if(isset($this->attributes['class_id'])){
            $value = ProductsCategory::where('id',$this->attributes['class_id'])->where('status',1)->value('name');
            return $value;
        }
        return $value;
    }

    public function getCategoryNameAttribute($value)
    {
        if(isset($this->attributes['category_id'])){
            $value = DictionaryValue::where('code','quote_cage')->where('value',$this->attributes['category_id'])->value('name');
            return $value;
        }
        return $value;
    }
}