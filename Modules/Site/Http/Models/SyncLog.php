<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class SyncLog extends Base {
    // 设置允许入库字段,数组形式
    protected $fillable
                     = ['count', 'ingore_count', 'insert_count', 'update_count', 'error_count',
                        'ingore_detail', 'update_detail', 'insert_detail', 'created_at', 'updated_at' , 'error_detail'];
    protected $table = 'sync_log';
    public    $ListSelect
                     = ['id', 'count', 'ingore_count', 'insert_count', 'update_count', 'error_count', 'created_at',
                        'updated_at'];
}
