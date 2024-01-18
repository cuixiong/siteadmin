<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Office extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['city', 'name','language_alias','region','area','image','national_flag','phone','address','working_language','working_time','time_zone','status','sort', 'updated_by', 'created_by'];
}