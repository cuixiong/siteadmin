<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Email extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','email','host','port','encryption','password','updated_by','created_by'];
}