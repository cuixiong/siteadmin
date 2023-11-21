<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Server extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','language_id','site_ids','ip','username','password','sort','status','bt_link','updated_by','created_by'];
}