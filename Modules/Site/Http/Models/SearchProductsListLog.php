<?php

namespace Modules\Site\Http\Models;

class SearchProductsListLog extends Base
{
    protected $table = 'search_products_list_log';

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'id',
        'ip',
        'ip_addr',
        'keywords',
        'status',
        'sort',
        'created_by',
        'updated_by',
    ];
}
