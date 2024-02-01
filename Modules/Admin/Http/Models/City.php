<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class City extends Base
{
    protected $table = 'citys';

    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'pid', 'country_id', 'type', 'status', 'sort', 'updated_by', 'created_by'];

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['country', 'type_text'];


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

        //pid
        if (isset($search->pid) && !empty($search->pid)) {
            $model = $model->where('pid', $search->pid);
        }

        //country_id
        if (isset($search->country_id) && !empty($search->picountry_idd)) {
            $model = $model->where('country_id', $search->country_id);
        }

        //type
        if (isset($search->type) && !empty($search->type)) {
            $model = $model->where('type', $search->type);
        }

        //sort
        if (isset($search->sort) && !empty($search->sort)) {
            $model = $model->where('sort', $search->sort);
        }

        //status 状态
        if (isset($search->status) && $search->status != '') {
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


    /**
     * 国家地区获取器
     */
    public function getCountryAttribute()
    {
        $text = '';
        if (isset($this->attributes['country_id'])) {
            return Country::getCountryName($this->attributes['country_id']);
        }
        return $text;
    }

    /**
     * 类型获取器
     */
    public function getTypeTextAttribute()
    {
        $text = '';
        if (isset($this->attributes['type'])) {
            $text = DictionaryValue::where('code', 'City_Type')->where('value', $this->attributes['type'])->value('name');
        }
        return $text ?? '';
    }

    
}
