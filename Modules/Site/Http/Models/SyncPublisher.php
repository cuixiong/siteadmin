<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class SyncPublisher extends Base {
    // 设置允许入库字段,数组形式
    protected $fillable
        = ['publisher_id', 'site_publisher_code', 'third_publisher_code'];
    protected $table = 'sync_publisher';
}
