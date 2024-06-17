<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class SyncField extends Base {
    protected $table = 'sync_field';
    // 设置允许入库字段,数组形式
    protected     $fillable = ['name', 'as_name', 'type', 'order', 'description', 'status', 'is_required', 'table'];
    public static $typeValues
                            = [
            1 => 'int',
            2 => 'string',
        ];
    public static $isRequiredValues
                            = [
            '1' => '必填',
            '0' => '非必填',
        ];
    public static $tableValues
                            = [
            '1' => '报告表',
            '2' => '报告描述表',
        ];
}
