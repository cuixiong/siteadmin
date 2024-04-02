<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class LanguageWebsite extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','url', 'status', 'sort', 'created_by', 'updated_by'];
}
