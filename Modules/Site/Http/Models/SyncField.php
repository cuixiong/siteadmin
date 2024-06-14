<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class SyncField extends Base {
    protected $table = 'sync_field';
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'as_name', 'type', 'order', 'description', 'status', 'is_required', 'table'];
}
