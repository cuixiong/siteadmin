<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Comment extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['title', 'image','notes','status','comment_at','sort', 'updated_by', 'created_by'];
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