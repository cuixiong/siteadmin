<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class PriceEdition extends Base
{
    // 设置可以入库的字段
    protected $fillable = ['publisher_id', 'order', 'status', 'created_by', 'updated_by',];

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['publisher'];



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
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $search)
    {
        if (!empty($search->publisher_id)) {
            $model = $model->whereRaw("FIND_IN_SET(?, publisher_id) > 0", [$search->publisher_id]);
        }
        if (isset($search->status)) {
            $model = $model->where('status', $search->status);
        }
        if (!empty($search->startTime)) {
            $startTime = strtotime($search->startTime);
            $model = $model->where('created_at', '>=', $startTime);
        }
        if (!empty($search->endTime)) {
            $endTime = strtotime($search->endTime);
            $model = $model->where('created_at', '<=', $endTime);
        }
        return $model;
    }
}
