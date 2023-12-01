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
        'discount_type',
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

    
    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request)
    {

        $search = json_decode($request->input('search'));
        //id 
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }

        //name
        if (isset($search->name) && !empty($search->name)) {
            $model = $model->where('link', 'name', '%' . $search->name . '%');
        }

        //link
        if (isset($search->link) && !empty($search->link)) {
            $model = $model->where('link', 'like', '%' . $search->link . '%');
        }

        //seo_title
        if (isset($search->seo_title) && !empty($search->seo_title)) {
            $model = $model->where('seo_title', 'like', '%' . $search->seo_title . '%');
        }
        //seo_keyword
        if (isset($search->seo_keyword) && !empty($search->seo_keyword)) {
            $model = $model->where('seo_keyword', 'like', '%' . $search->seo_keyword . '%');
        }
        //seo_description
        if (isset($search->seo_description) && !empty($search->seo_description)) {
            $model = $model->where('seo_description', 'like', '%' . $search->seo_description . '%');
        }

        //email
        if (isset($search->email) && !empty($search->email)) {
            $model = $model->where('email', 'like', '%' . $search->email . '%');
        }

        //keyword_suffix
        if (isset($search->keyword_suffix) && !empty($search->keyword_suffix)) {
            $model = $model->where('keyword_suffix', 'like', '%' . $search->keyword_suffix . '%');
        }

        //product_tag
        if (isset($search->product_tag) && !empty($search->product_tag)) {
            $model = $model->where('product_tag', 'like', '%' . $search->product_tag . '%');
        }

        //discount 
        if (isset($search->discount) && $search->discount != '') {
            $model = $model->where('discount', $search->discount);
        }

        //discount_amount
        if (isset($search->discount_amount) && $search->discount_amount != '') {
            $model = $model->where('discount_amount', $search->discount_amount);
        }

        //discount_type
        if (isset($search->discount_type) && $search->discount_type != '') {
            $model = $model->where('discount_type', $search->discount_type);
        }

        //status 状态
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }

        //show_home 状态
        if (isset($search->show_home) && $search->show_home != '') {
            $model = $model->where('show_home', $search->show_home);
        }

        //时间为数组形式
        //创建时间
        if (isset($search->created_at) && !empty($search->created_at)) {
            $createTime = $search->created_at;
            $model = $model->where('created_at', '>=', $createTime[0]);
            $model = $model->where('created_at', '<=', $createTime[1]);
        }

        //更新时间
        if (isset($search->updated_at) && !empty($search->updated_at)) {
            $updateTime = $search->updated_at;
            $model = $model->where('updated_at', '>=', $updateTime[0]);
            $model = $model->where('updated_at', '<=', $updateTime[1]);
        }

        
        //折扣开始时间
        if (isset($search->discount_time_begin) && !empty($search->discount_time_begin)) {
            $discountTimeBegin = $search->discount_time_begin;
            $model = $model->where('discount_time_begin', '>=', $discountTimeBegin[0]);
            $model = $model->where('discount_time_begin', '<=', $discountTimeBegin[1]);
        }
        
        //折扣结束时间
        if (isset($search->discount_time_end) && !empty($search->discount_time_end)) {
            $discountTimeEnd = $search->discount_time_end;
            $model = $model->where('discount_time_end', '>=', $discountTimeEnd[0]);
            $model = $model->where('discount_time_end', '<=', $discountTimeEnd[1]);
        }

        return $model;
    }
}
