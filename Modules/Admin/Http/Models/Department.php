<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Department extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['parent_id','name','sort','status','created_by','updated_by','default_role'];
}