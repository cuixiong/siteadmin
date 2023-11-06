<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class PriceEditionValue extends Base
{
    // 设置可以入库的字段
    protected $fillable = ['name', 'edition_id', 'language_id', 'rules', 'notice', 'order', 'status', 'is_logistics', 'created_by', 'updated_by',];
}
