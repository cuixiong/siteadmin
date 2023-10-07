<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
class Publisher extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['name','email','phone','company','province_id','city_id','logo','address','link','content','status','created_at','updated_at','updated_by','created_by'];

}
