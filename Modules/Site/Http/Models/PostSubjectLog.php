<?php

namespace Modules\Site\Http\Models;

use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Models\Base;

class PostSubjectLog extends Base
{
    protected $table = 'post_subject_log';

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'file', 
        'type', 
        'details', 
        'error_details', 
        'ingore_details', 
        'post_subject_id', 
        'post_subject_count', 
        'success_count', 
        'ingore_count', 
        'error_count', 
        'created_by', 
        'updated_by',
    ];


    protected $attributes = [
        'status' => 1,
        'sort' => 100,
    ];
    
    const POST_SUBJECT_CURD = 1; // 课题修改
    const POST_SUBJECT_ACCEPT = 2; // 领取分配
    const POST_SUBJECT_EXPORT = 3; // 导出课题
    const POST_SUBJECT_LINK_EXPORT = 4; // 导出日志(链接)
    const POST_SUBJECT_LINK_UPLOAD = 5; // 上传日志
    const POST_SUBJECT_LINK_UPLOAD_OLD = 6; // 旧课题上传
    const POST_SUBJECT_STRATEGY_ACCEPT = 7; // 策略分配

    public static function getLogTypeList()
    {
        return [
            self::POST_SUBJECT_CURD => '课题修改',
            self::POST_SUBJECT_ACCEPT => '领取分配',
            self::POST_SUBJECT_EXPORT => '导出课题',
            self::POST_SUBJECT_LINK_EXPORT => '导出日志',
            self::POST_SUBJECT_LINK_UPLOAD => '上传日志',
            self::POST_SUBJECT_LINK_UPLOAD_OLD => '旧课题上传',
            self::POST_SUBJECT_STRATEGY_ACCEPT => '策略分配',
        ];
    }

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request,)
    {
        $search = json_decode($request->input('search'));
        // id 
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }

        // post_subject_id
        if (isset($search->post_subject_id) && !empty($search->post_subject_id)) {
            $model = $model->where('post_subject_id', $search->post_subject_id);
        }

        // type
        if (isset($search->type) && !empty($search->type)) {
            $model = $model->where('type', $search->type);
        }

        if (isset($search->post_subject_count) && !empty($search->post_subject_count)) {
            $model = $model->where('post_subject_count', $search->post_subject_count);
        }
        // success_count
        if (isset($search->success_count) && !empty($search->success_count)) {
            $model = $model->where('success_count', $search->success_count);
        }

        // ingore_count
        if (isset($search->ingore_count) && !empty($search->ingore_count)) {
            $model = $model->where('ingore_count', $search->ingore_count);
        }

        if (isset($search->error_count) && !empty($search->error_count)) {
            $model = $model->where('error_count', $search->error_count);
        }
        
        if (isset($search->created_by) && !empty($search->created_by)) {
            $userIds = User::where('nickname', 'like', '%'.($search->created_by).'%')->pluck('id');
            $userIds = $userIds ? $userIds : [];
            $model = $model->whereIn('created_by', $userIds);
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
