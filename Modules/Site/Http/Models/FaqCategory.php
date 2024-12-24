<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class FaqCategory extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'status'];
    protected $table = 'faq_category';
}
