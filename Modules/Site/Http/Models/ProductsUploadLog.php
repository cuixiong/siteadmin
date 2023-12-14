<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class ProductsUploadLog extends Base
{
    protected $table = 'product_upload_log';

    const UPLOAD_INIT = 0;  //上传未开始
    const UPLOAD_READY = 1; // 文件加载好了
    const UPLOAD_RUNNING = 2;  //正在运行
    const UPLOAD_COMPLETE = 3;  //上传结束

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'file',
        'count',
        'insert_count',
        'update_count',
        'error_count',
        'details',
        'state',
    ];

    protected $attributes = [
        'count' => 0,
        'insert_count' => 0,
        'update_count' => 0,
        'error_count' => 0,
        'state' => self::UPLOAD_INIT,
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

        //file
        if (isset($search->file) && !empty($search->file)) {
            $model = $model->where('file', 'like', '%' . $search->file . '%');
        }

        //state 
        if (isset($search->state) && $search->state != '') {
            $model = $model->where('state', $search->state);
        }

        //count 
        if (isset($search->count) && $search->count != '') {
            $model = $model->where('count', $search->count);
        }

        //insert_count 
        if (isset($search->insert_count) && $search->insert_count != '') {
            $model = $model->where('insert_count', $search->insert_count);
        }

        //update_count 
        if (isset($search->update_count) && $search->update_count != '') {
            $model = $model->where('update_count', $search->update_count);
        }

        //error_count
        if (isset($search->error_count) && $search->error_count != '') {
            $model = $model->where('error_count', $search->error_count);
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
