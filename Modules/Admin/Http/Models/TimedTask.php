<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class TimedTask extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name', 
        'type', 
        'content',
        'status',
        'sort',
        'log_path',
        'site_id',
        'day',
        'hour',
        'minutes',
        'week_day',
        'log_path',
        'time_type', 
        'updated_by', 
        'created_by',
        'category',
        'command',
        'parent_id'
    ];
}
