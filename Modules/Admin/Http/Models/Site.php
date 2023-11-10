<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
use PDO;

class Site extends Base
{
    // 设置可以入库的字段
    protected $fillable = [
        'name', 
        'english_name', 
        'domain', 
        'country_id', 
        'publisher_id', 
        'language_id', 
        'status', 
        'database_id',
        'server_id',
        // 'db_host', 
        // 'db_port', 
        // 'db_database', 
        // 'db_username', 
        // 'db_password', 
        'updated_by', 
        'created_by'
    ];

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['publisher', 'country', 'language'];

    /**
     * 出版商获取器
     */
    public function getPublisherAttribute()
    {
        $text = '';
        if (isset($this->attributes['publisher_id'])) {
            $publisherIds = explode(',', $this->attributes['publisher_id']);
            $text = Publisher::whereIn('id', $publisherIds)->pluck('name')->toArray();
            $text = implode(';', $text);
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
            $text = Region::where('id', $this->attributes['country_id'])->value('name');
        }
        return $text;
    }

    /**
     * 语言获取器
     */
    public function getLanguageAttribute()
    {
        $text = '';
        if (isset($this->attributes['language_id'])) {
            $text = Language::where('id', $this->attributes['language_id'])->value('name');
        }
        return $text;
    }


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
            $model = $model->where('name', 'like', '%' . $search->name . '%');
        }

        //domain
        if (isset($search->domain) && !empty($search->domain)) {
            $model = $model->where('domain', 'like', '%' . $search->domain . '%');
        }

        //english_name
        if (isset($search->english_name) && !empty($search->english_name)) {
            $model = $model->where('english_name', 'like', '%' . $search->english_name . '%');
        }

        //publisher_id 出版商
        if (isset($search->publisher_id) && !empty($search->publisher_id)) {
            $model = $model->whereRaw("FIND_IN_SET(?, publisher_id) > 0", [$search->publisher_id]);
        }

        //country_id 国家地区
        if (isset($search->country_id) && !empty($search->country_id)) {
            $model = $model->where('country_id', $search->country_id);
        }

        //language_id 语言
        if (isset($search->language_id) && !empty($search->language_id)) {
            $model = $model->where('language_id', $search->language_id);
        }

        //status 状态
        if (isset($search->status) && !empty($search->status)) {
            $model = $model->where('status', $search->status);
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

        return $model;
    }

    
    //打印sql
    public function printSql($model){
        $sql = $model->toSql();
        $bindings = $model->getBindings();

        // 替换问号占位符
        foreach ($bindings as $binding) {
            $sql = preg_replace('/\?/', "'$binding'", $sql, 1);
        }
        return $sql;
        
    }
}
