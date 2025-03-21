<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class PostSubjectArticleLink extends Base
{
    protected $table = 'post_subject_article_link';

    // 设置允许入库字段,数组形式
    protected $fillable = ['post_subject_id', 'link', 'post_platform_id', 'status', 'sort', 'updated_by', 'created_by'];

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
        // id 
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }

        // link
        if (isset($search->link) && !empty($search->link)) {
            $model = $model->where('link', 'like', '%' . $search->link . '%');
        }

        // post_platform_id
        if (isset($search->post_platform_id) && !empty($search->post_platform_id)) {
            $model = $model->where('post_platform_id', $search->post_platform_id);
        }
        

        // sort
        if (isset($search->sort) && !empty($search->sort)) {
            $model = $model->where('sort', $search->sort);
        }

        // status 状态
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
