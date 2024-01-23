<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Partner extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'logo','status','sort', 'updated_by', 'created_by'];

    // logo修改器
    public function setLogoAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['logo'] = $value;
        return $value;
    }
    // logo获取器
    public function getLogoAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }
}