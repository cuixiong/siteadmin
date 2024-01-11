<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Qualification extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'image','status','sort', 'updated_by', 'created_by'];
}