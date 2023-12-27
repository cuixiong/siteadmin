<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Email extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','email','host','port','encryption','password','status','updated_by','created_by','sort'];
}