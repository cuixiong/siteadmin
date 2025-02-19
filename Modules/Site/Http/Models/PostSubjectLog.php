<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class PostSubjectLog extends Base
{
    protected $table = 'post_subject_log';

    // 设置允许入库字段,数组形式
    protected $fillable = ['type', 'details', 'post_subject_id', 'created_by', 'updated_by',];

    const POST_SUBJECT_CURD = 1; // 增删改查
    const POST_SUBJECT_ACCEPT = 2; // 领取分配
    const POST_SUBJECT_EXPORT = 3; // 导出课题
    const POST_SUBJECT_LINK_EXPORT = 4; // 导出日志(链接)
    const POST_SUBJECT_LINK_UPLOAD = 5; // 上传日志


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

        // type
        if (isset($search->type) && !empty($search->type)) {
            $model = $model->where('type', $search->type);
        }

        //时间为数组形式
        //创建时间
        if (isset($search->created_at) && !empty($search->created_at)) {
            $createTime = $search->created_at;
            $model = $model->where('created_at', '>=', $createTime[0]);
            $model = $model->where('created_at', '<=', $createTime[1]);
        }

        return $model;
    }
}
