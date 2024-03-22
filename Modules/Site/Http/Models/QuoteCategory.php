<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class QuoteCategory extends Base
{
    protected $table = 'quote_categorys';

    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'sort','status','updated_by', 'created_by'];

}
