<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User as Admin;

class News extends Base
{

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['category', 'type_text','upload_at_format'];

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'type',         // 新闻类型
        'category_id',  // 行业分类
        'title',        // 标题
        'description',  // seo描述/简述
        'thumb',        // 封面图片
        'url',          // 自定义链接
        'content',      // 内容
        'sort',         // 排序
        'show_home',    // 是否显示在首页
        'status',       // 状态
        'created_by',   // 创建者
        'updated_by',   // 编辑者
        'hits',         // 虚拟点击数
        'real_hits',    // 真实点击数
        'keywords',     // 关键词
        'tags',         // 标签
        'upload_at',    // 自定义显示时间，不到此时间不会显示在网站上
        'author',       // 自定义作者，保留字段

    ];

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request)
    {

        $search = json_decode($request->input('search'), true);


        if (!empty($search)) {
            $textField = ['title', 'description', 'thumb', 'url', 'content', 'keywords', 'tags', 'author'];
            $numberField = ['id', 'type', 'category_id', 'upload_at', 'sort', 'show_home', 'status', 'hits', 'real_hits'];
            $timeField = ['created_at', 'updated_at', 'upload_at'];
            $userField = ['created_by', 'updated_by'];
            foreach ($search as $key => $value) {
                if (in_array($key, $textField)  && $value != '') {
                    $model = $model->where($key, 'like', '%' . trim($value) . '%');
                } else if (in_array($key, $numberField) && $value != '') {
                    $model = $model->where($key, $value);
                } else if (in_array($key, $timeField) && !empty($value)) {
                    $time = $value;
                    $model = $model->where($key, '>=', $time[0]);
                    $model = $model->where($key, '<=', $time[1]);
                } else if (in_array($key, $userField) && !empty($value)) {

                    $userIds = Admin::where('name', 'like', '%' . $value . '%')->pluck('id');
                    $userIds = $userIds ? $userIds : [];
                    $model = $model->whereIn($key, $userIds);
                }
            }
        }
        return $model;
    }

    /**
     * 行业分类获取器
     */
    public function getCategoryAttribute()
    {
        $text = '';
        if (isset($this->attributes['category_id'])) {
            return ProductsCategory::query()->where('id',$this->attributes['category_id'])->value('name') ?? '';
        }
        return $text;
    }

    /**
     * 新闻类型获取器
     */
    public function getTypeTextAttribute()
    {
        $text = '';
        if (isset($this->attributes['type'])) {
            // 新闻类型
            $text = NewsCategory::query()->where('id', $this->attributes['type'])->value('name') ?? '';
        }
        return $text;
    }
    
    /**
     * 上传时间获取器
     */
    public function getUploadAtFormatAttribute()
    {
        $text = '';
        if (isset($this->attributes['upload_at']) && !empty($this->attributes['upload_at'])) {
            return date('Y-m-d H:i:s', $this->attributes['upload_at']);
        }
        return $text;
    }
}
