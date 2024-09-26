<?php

namespace Modules\Site\Http\Models;


class UserAddress extends Base {
    public $table = 'user_address';
    // 设置允许入库字段,数组形式
    protected $fillable
        = [
            'user_id',
            'sort',
            'is_default',
            'status',
            'consignee',
            'contact_number',
            'email',
            'country_id',
            'province_id',
            'city_id',
            'address',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
}
