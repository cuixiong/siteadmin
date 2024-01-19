<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Plate extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name', 
        'alias', 
        'page_id', 
        'title',
        'short_title',
        'pc_image',
        'mb_image',
        'content',
        'status',
        'sort', 
        'updated_by', 
        'created_by'
    ];

    protected $appends = [
        'page_name'
    ];

    // pc_image修改器
    public function setPcImageAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['pc_image'] = $value;
        return $value;
    }
    // pc_image获取器
    public function getPcImageAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }
    // mb_image修改器
    public function setMbImageAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['mb_image'] = $value;
        return $value;
    }
    // mb_image获取器
    public function getMbImageAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }
    // page_name获取器
    public function getPageNameAttribute($value){
        if(isset($this->attributes['page_id'])){
            $value = Menu::where('id',$this->attributes['page_id'])->value('name');
            $value = $value ? $value : '';
            return $value;
        }
        return '';
    }
}