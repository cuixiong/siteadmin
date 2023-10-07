<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Site extends Base
{
    // 设置可以入库的字段
    protected $fillable = ['name','english_name','domain','country_id','publisher_id','language_id','status','db_host','db_port','db_database','db_username','db_password','updated_by','created_by']; 
}