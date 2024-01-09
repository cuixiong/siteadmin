<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class PlateValue extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['parent_id','title', 'short_title', 'link', 'alias','image','icon','content','sort','status', 'updated_by', 'created_by'];
}