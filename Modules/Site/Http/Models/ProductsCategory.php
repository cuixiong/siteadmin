<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class ProductsCategory extends Base
{
    protected $table = 'product_category';

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name',
        'link',
        'thumb',
        'home_thumb',
        'icon',
        'sort',
        'status',
        'discount',
        'discount_amount',
        'discount_time_begin',
        'discount_time_end',
        'seo_title',
        'seo_keyword',
        'seo_description',
        'show_home',
        'email',
        'keyword_suffix',   //关键词后缀
        'product_tag',  //产品标签
    ];
}
