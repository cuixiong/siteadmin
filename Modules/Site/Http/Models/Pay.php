<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Pay extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'image', 'pay_code', 'info_login', 'info_key','return_url','notify_url','sign','status','sort','content', 'updated_by', 'created_by'];

    public function setImageAttribute($value)
    {
        $value = is_array($value) && !empty($value) ? implode(',',$value) : "";
        $this->attributes['image'] = $value;
        return $value;
    }

    public function getImageAttribute($value)
    {
        $value = !empty($value) ? explode(',',$value) : [];
        return $value;
    }
}