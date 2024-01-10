<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Authority extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'updated_by', 'created_by'];
}