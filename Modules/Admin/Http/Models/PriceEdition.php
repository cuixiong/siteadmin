<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;

class PriceEdition extends Base
{
    // 设置可以入库的字段
    protected $fillable = ['publisher_id','order','status','created_by','updated_by',]; 

}