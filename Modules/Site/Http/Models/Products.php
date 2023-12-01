<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class Products extends Base
{
    protected $table = 'product_routine';

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
        'discount',     //折扣，百分比
        'discount_amount',  //折扣，直减
        'discount_time_begin',  //折扣开始时间
        'discount_time_end', //折扣结束时间
        "have_sample",  //是否有样本
        //各类状态
        'show_home',    //首页显示
        'show_hot',    // 热门
        'show_recommend' // 推荐
    ];
}
