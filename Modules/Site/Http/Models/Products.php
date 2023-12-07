<?php

namespace Modules\Site\Http\Models;

use Modules\Admin\Http\Models\Country;
use Modules\Site\Http\Models\Base;

class Products extends Base
{
    protected $table = 'product_routine';

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['category', 'country','published_date_format'];

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name', //报告名称
        'english_name', //报告名称
        'thumb',    //图片,无则显示分类图片
        'category_id',  //行业id
        'country_id', //国家区域id
        'price',    //基础价
        'status',   //状态
        'keywords',  //关键词
        'url',  //自定义链接
        'published_date', //出版日期
        'sort', //排序
        'updated_by',   //修改者
        'created_by',   //创建者
        'author',   //报告作者
        'pages',      //页数
        'tables',   //图表数
        'hits',         //点击量
        'downloads',  //下载量
        'discount',     //折扣率
        'discount_amount',  //折扣金额
        'discount_type',  //折扣类型
        'discount_time_begin',  //折扣开始时间
        'discount_time_end', //折扣结束时间
        "have_sample",  //是否有样本
        //各类状态
        'show_home',    //首页显示
        'show_hot',    // 热门
        'show_recommend' // 推荐
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

        // name
        if (isset($search->name) && !empty($search->name)) {
            $model = $model->where('name', 'like', '%' . $search->name . '%');
        }

        // english_name
        if (isset($search->english_name) && !empty($search->english_name)) {
            $model = $model->where('english_name', 'like', '%' . $search->english_name . '%');
        }

        // category_id 
        if (isset($search->category_id) && $search->category_id != '') {
            $model = $model->where('category_id', $search->category_id);
        }

        // country_id 
        if (isset($search->country_id) && $search->country_id != '') {
            $model = $model->where('country_id', $search->country_id);
        }

        // price 
        if (isset($search->price) && $search->price != '') {
            $model = $model->where('price', $search->price);
        }

        // status 状态
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }
        
        // keywords
        if (isset($search->keywords) && !empty($search->keywords)) {
            $model = $model->where('keywords', 'like', '%' . $search->keywords . '%');
        }

        // url
        if (isset($search->url) && !empty($search->url)) {
            $model = $model->where('url', 'like', '%' . $search->url . '%');
        }
        
        // sort 
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }

        // author
        if (isset($search->author) && !empty($search->author)) {
            $model = $model->where('author', 'like', '%' . $search->author . '%');
        }

        // pages
        if (isset($search->pages) && $search->pages != '') {
            $model = $model->where('pages', $search->pages);
        }
        // tables
        if (isset($search->tables) && $search->tables != '') {
            $model = $model->where('tables', $search->tables);
        }
        // hits
        if (isset($search->hits) && $search->hits != '') {
            $model = $model->where('hits', $search->hits);
        }
        // downloads
        if (isset($search->downloads) && $search->downloads != '') {
            $model = $model->where('downloads', $search->downloads);
        }

        // discount 
        if (isset($search->discount) && $search->discount != '') {
            $model = $model->where('discount', $search->discount);
        }
        // discount_amount
        if (isset($search->discount_amount) && $search->discount_amount != '') {
            $model = $model->where('discount_amount', $search->discount_amount);
        }

        // discount_type
        if (isset($search->discount_type) && $search->discount_type != '') {
            $model = $model->where('discount_type', $search->discount_type);
        }

        // have_sample
        if (isset($search->have_sample) && $search->have_sample != '') {
            $model = $model->where('have_sample', $search->have_sample);
        }

        // show_home 状态
        if (isset($search->show_home) && $search->show_home != '') {
            $model = $model->where('show_home', $search->show_home);
        }
        // show_hot 状态
        if (isset($search->show_hot) && $search->show_hot != '') {
            $model = $model->where('show_hot', $search->show_hot);
        }
        
        // show_recommend 状态
        if (isset($search->show_recommend) && $search->show_recommend != '') {
            $model = $model->where('show_recommend', $search->show_recommend);
        }

        // 时间为数组形式
        // 创建时间
        if (isset($search->created_at) && !empty($search->created_at)) {
            $createTime = $search->created_at;
            $model = $model->where('created_at', '>=', $createTime[0]);
            $model = $model->where('created_at', '<=', $createTime[1]);
        }

        // 更新时间
        if (isset($search->updated_at) && !empty($search->updated_at)) {
            $updateTime = $search->updated_at;
            $model = $model->where('updated_at', '>=', $updateTime[0]);
            $model = $model->where('updated_at', '<=', $updateTime[1]);
        }

        // 出版时间
        if (isset($search->published_date) && !empty($search->published_date)) {
            $publishedTime = $search->published_date;
            $model = $model->where('published_date', '>=', $publishedTime[0]);
            $model = $model->where('published_date', '<=', $publishedTime[1]);
        }

        
        // 折扣开始时间
        if (isset($search->discount_time_begin) && !empty($search->discount_time_begin)) {
            $discountTimeBegin = $search->discount_time_begin;
            $model = $model->where('discount_time_begin', '>=', $discountTimeBegin[0]);
            $model = $model->where('discount_time_begin', '<=', $discountTimeBegin[1]);
        }
        
        // 折扣结束时间
        if (isset($search->discount_time_end) && !empty($search->discount_time_end)) {
            $discountTimeEnd = $search->discount_time_end;
            $model = $model->where('discount_time_end', '>=', $discountTimeEnd[0]);
            $model = $model->where('discount_time_end', '<=', $discountTimeEnd[1]);
        }

        return $model;
    }

    
    /**
     * 分类获取器
     */
    public function getCategoryAttribute()
    {
        $text = '';
        if (isset($this->attributes['category_id'])) {
            $text = ProductsCategory::where('id', $this->attributes['category_id'])->value('name')??'';
        }
        return $text;
    }

    /**
     * 国家地区获取器
     */
    public function getCountryAttribute()
    {
        $text = '';
        if (isset($this->attributes['country_id'])) {
            $text = Country::whereIn('id', $this->attributes['country_id'])->value('name');
        }
        return $text;
    }
    
    /**
     * 出版时间获取器
     */
    public function getPublishedDateFormatAttribute()
    {
        return date('Y-m-d H:i:s',$this->attributes['published_date']);
    }

    
    public static function publishedDateFormatYear($timestamp)
    {

        $year = date('Y', $timestamp);
        if (empty($year) || !is_numeric($year) || strlen($year) !== 4) {
            return false;
        }
        return $year;
    }


}
