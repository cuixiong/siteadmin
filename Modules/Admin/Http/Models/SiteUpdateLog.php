<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class SiteUpdateLog extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['site_id','english_name','message','status','created_at','created_by','updated_at','updated_by'];
}
