<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Office extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'city',
        'name',
        'language_alias',
        'region',
        'area',
        'image',
        'national_flag',
        'phone',
        'address',
        'post',
        'email',
        'website',
        'working_language',
        'working_language_status',
        'working_time',
        'working_time_status',
        'time_zone',
        'time_zone_status',
        'status',
        'sort',
        'updated_by',
        'created_by'
    ];

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

    // NationalFlag修改器
    public function setNationalFlagAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['national_flag'] = $value;
        return $value;
    }
    // NationalFlag获取器
    public function getNationalFlagAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }
}
