<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class PersonalSetting extends Base
{
    protected $table = 'personal_setting';

    // 设置允许入库字段,数组形式
    protected $fillable = ['key', 'value', 'user_id', 'status', 'sort', 'updated_by', 'created_by'];

    protected $attributes = [
        'status' => 1,
        'sort' => 100,
    ];
}
