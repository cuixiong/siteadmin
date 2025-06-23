<?php

namespace Modules\Admin\Http\Models;

class SyncSiteLog extends Base {
    protected $table = 'sync_site_log';
    // 设置允许入库字段,数组形式
    protected $fillable
        = [
            'site_name',
            'site_id',
            'sort',
            'event_name',
            'event_type',
            'status',
        ];
}
