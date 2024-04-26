<?php

namespace Modules\Site\Http\Models;



use Illuminate\Database\Eloquent\Model;

class XunsearchProductIndex extends Model {
    protected $table = 'xunsearch_product_index';
    public $timestamps = false;
    // 设置允许入库字段,数组形式
    protected $fillable = ['id', 'name', 'english_name', 'category_id', 'country_id', 'price', 'keywords', 'url', 'published_date', 'status', 'author', 'discount', 'discount_amount', 'show_hot', 'show_recommend', 'sort', 'description'];
}
