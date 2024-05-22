<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\Country;

class User extends Base {
    // 设置允许入库字段,数组形式
    protected $fillable
        = ['name', 'username', 'email', 'phone', 'area_id', 'status', 'company', 'check_email', 'login_time',
           'updated_by', 'created_by'];
    // 添加虚拟字段
    protected $appends = ['area_name', 'login_time'];

    public function getAreaNameAttribute() {
        if (isset($this->attributes['area_id'])) {
            $value = Country::where('status', 1)->where('id', $this->attributes['area_id'])->value('name');
            $value = $value ? $value : "";
        } else {
            $value = "";
        }

        return $value;
    }

    public function getLoginTimeAttribute() {
        if (!empty($this->attributes['login_time'])) {
            return date("Y-m-d", $this->attributes['login_time']);
        } else {
            return '';
        }
    }
}
