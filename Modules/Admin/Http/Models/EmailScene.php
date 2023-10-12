<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class EmailScene extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','title','body','email_sender_id','email_recipient','status','sort','updated_by','created_by'];
}