<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Applyfor extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'email', 'company', 'country','source','status','updated_by', 'created_by'];
}