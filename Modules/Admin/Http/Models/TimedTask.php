<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class TimedTask extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'type', 'content','status','sort', 'updated_by', 'created_by'];
}
