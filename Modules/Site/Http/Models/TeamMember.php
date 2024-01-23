<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class TeamMember extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name',
        'image',
        'position',
        'status',
        'sort', 
        'updated_by', 
        'created_by',
        'describe',
        'area',
        'experience',
        'custom',
        'industry',
        'is_analyst'
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
}