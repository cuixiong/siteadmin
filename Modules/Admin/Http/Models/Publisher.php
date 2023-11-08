<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
class Publisher extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = [
        'name',
        'short_name',
        'email',
        'phone',
        'company',
        'province_id',
        'city_id',
        'logo',
        'address',
        'link',
        'content',
        'status',
        'created_at',
        'updated_at',
        'updated_by',
        'created_by',
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
        
        //company
        if (isset($search->company) && !empty($search->company)) {
            $model = $model->where('name', 'like', '%' . $search->company . '%');
        }
        
        //content
        if (isset($search->content) && !empty($search->content)) {
            $model = $model->where('content', 'like', '%' . $search->content . '%');
        }
        
        //order
        if (isset($search->order) && !empty($search->order)) {
            $model = $model->where('order', $search->order);
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

}
