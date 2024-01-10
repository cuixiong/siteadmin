<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Plate extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'alias', 'type', 'title','short_title','pc_image','mb_image','content','status','sort', 'updated_by', 'created_by'];
}