<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class CurrencyConfig extends Base
{
    protected $table = 'currency_config';

    // 设置允许入库字段,数组形式
    protected $fillable = ['code', 'is_first', 'exchange_rate', 'tax_rate', 'is_show', 'status', 'sort', 'updated_by', 'created_by'];

    protected $attributes = [
        'status' => 1,
        'sort' => 100,
    ];
}
