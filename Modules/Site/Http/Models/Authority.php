<?php

namespace Modules\Site\Http\Models;
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
        'class_name',
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
}