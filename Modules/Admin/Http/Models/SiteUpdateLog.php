<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class SiteUpdateLog extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['site_id', 'site_name', 'command', 'message', 'output', 'exec_status', 'hash', 'hash_sample', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'sort'];

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['exec_status_text'];


    /**
     * 更新状态获取器
     */
    public function getExecStatusTextAttribute()
    {

        $text = '';
        if (isset($this->attributes['exec_status'])) {
            $logisticsTxtArray = array_column(SelectTxt::GetUpgradeTxt(), 'name', 'id');
            $text = $logisticsTxtArray[$this->attributes['exec_status']] ?? '';
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

        //site_id
        if (isset($search->site_id) && !empty($search->site_id)) {
            $model = $model->where('site_id', $search->site_id);
        }
        
        //site_name
        if (isset($search->site_name) && !empty($search->site_name)) {
            $model = $model->where('site_name', 'like', '%' . $search->site_name . '%');
        }

        //message
        if (isset($search->message) && !empty($search->message)) {
            $model = $model->where('message', 'like', '%' . $search->message . '%');
        }

        //output
        if (isset($search->output) && !empty($search->output)) {
            $model = $model->where('output', 'like', '%' . $search->output . '%');
        }

        //hash git执行commit后的hash值
        if (isset($search->hash) && !empty($search->hash)) {
            $model = $model->where('hash', 'like', '%' . $search->hash . '%');
        }

        //hash_sample 简短hash
        if (isset($search->hash_sample) && !empty($search->hash_sample)) {
            $model = $model->where('hash_sample', 'like', '%' . $search->hash_sample . '%');
        }

        //command 
        if (isset($search->command) && !empty($search->command)) {
            $model = $model->where('command', 'like', '%' . $search->command . '%');
        }

        //exec_status 执行状态
        if (isset($search->exec_status) && $search->exec_status != '') {
            $model = $model->where('exec_status', $search->exec_status);
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
