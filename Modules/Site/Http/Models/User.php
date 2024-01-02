<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class User extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'user_name', 'email', 'phone','coutry','status','company','login_time', 'updated_by', 'created_by'];
}