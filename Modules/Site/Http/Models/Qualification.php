<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Qualification extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'image','thumbnail','status','sort', 'updated_by', 'created_by'];

    // Image修改器
    public function setImageAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['image'] = $value;
        return $value;
    }
    // Image获取器
    public function getImageAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }

    // Thumbnail修改器
    public function setThumbnailAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['thumbnail'] = $value;
        return $value;
    }
    // Thumbnail获取器
    public function getThumbnailAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }
}