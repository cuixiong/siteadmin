<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Office extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'image','address','phone','wechat','language','work_time','status','sort', 'updated_by', 'created_by'];
}