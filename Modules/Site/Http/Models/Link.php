<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Link extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'logo','link','status','sort', 'updated_by', 'created_by'];

    public function setLogoAttribute($value)
    {
        $value = is_array($value) ? implode(",",$value) : $value;
        $this->attributes['logo'] = $value;
        return $value;
    }

    public function getLogoAttribute($value)
    {
        $value = $value ? explode(",",$value) : [];
        return $value;
    }
}