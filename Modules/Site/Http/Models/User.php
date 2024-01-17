<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\Country;

class User extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'user_name', 'email', 'phone','country_id','status','company','login_time', 'updated_by', 'created_by'];
    // 添加虚拟字段
    protected $appends = ['country'];

    public function getCountryAttribute()
    {
        if(isset($this->attributes['country_id'])){
            $value = Country::where('status',1)->where('id',$this->attributes['country_id'])->value('name');
            $value = $value ? $value : "";
        } else {
            $value = "";
        }
        return $value;
    }
}