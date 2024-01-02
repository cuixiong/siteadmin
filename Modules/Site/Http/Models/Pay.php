<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Pay extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'image', 'info_logo', 'info_key','return_url','notify_url','sign','status','sort','content', 'updated_by', 'created_by'];
}