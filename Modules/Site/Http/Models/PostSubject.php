<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class PostSubject extends Base
{
    protected $table = 'post_subject';

    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'product_id', 'product_category_id', 'analyst', 'version', 'propagate_status', 'last_propagate_time', 'accepter','accept_time', 
    'accept_status', 'status', 'sort', 'updated_by', 'created_by'];

    protected $attributes = [
        'status' => 1,
        'sort' => 100,
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
            $model = $model->where('name', 'like', '%' . $search->name . '%');
        }

        //product_category_id
        if (isset($search->product_category_id) && !empty($search->product_category_id)) {
            $model = $model->where('product_category_id', $search->product_category_id);
        }
        //analyst
        if (isset($search->analyst) && !empty($search->analyst)) {
            $model = $model->where('analyst', 'like', '%' . $search->analyst . '%');
        }
        // propagate_status 宣传状态
        if (isset($search->propagate_status) && !empty($search->propagate_status)) {
            $model = $model->where('propagate_status', $search->propagate_status);
        }

        // accepter 领取者
        if (isset($search->accepter) && !empty($search->accepter)) {
            $model = $model->where('accepter', $search->accepter);
        }
        // accept_status 领取状态
        if (isset($search->accept_status) && $search->accept_status != '') {
            $model = $model->where('accept_status ', $search->accept_status);
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

        // 领取时间
        if (isset($search->accept_time) && !empty($search->accept_time)) {
            $acceptTime = $search->accept_time;
            $model = $model->where('accept_time', '>=', $acceptTime[0]);
            $model = $model->where('accept_time', '<=', $acceptTime[1]);
        }

        //最后宣传时间
        if (isset($search->last_propagate_time) && !empty($search->last_propagate_time)) {
            $lastTime = $search->last_propagate_time;
            $model = $model->where('last_time', '>=', $lastTime[0]);
            $model = $model->where('last_time', '<=', $lastTime[1]);
        }
        return $model;
    }
}
