<?php

namespace Modules\Site\Http\Models;
class ViewProductsExportLog extends Base {
    protected $table = 'view_product_export_log';
    const UPLOAD_INIT     = 0;  //上传未开始
    const UPLOAD_READY    = 1; // 文件加载好了
    const UPLOAD_RUNNING  = 2;  //正在运行
    const UPLOAD_COMPLETE = 3;  //上传结束



    const EXPORT_INIT     = 0;  // 导出未开始
    const EXPORT_RUNNING  = 1;  //正在运行
    const EXPORT_MERGING  = 2;  //合并中
    const EXPORT_COMPLETE = 3;  //导出结束
    // 设置允许入库字段,数组形式
    protected $fillable
        = [
            'file',
            'count',
            'success_count',
            'error_count',
            'details',
            'state',
        ];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['state_text',];
    protected $attributes
                       = [
            'count'         => 0,
            'success_count' => 0,
            'error_count'   => 0,
            'state'         => self::EXPORT_INIT,
        ];

    /**
     * 处理查询列表条件数组
     *
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request) {
        $search = json_decode($request->input('search'));
        //id
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }
        //file
        if (isset($search->file) && !empty($search->file)) {
            $model = $model->where('file', 'like', '%'.$search->file.'%');
        }
        //state
        if (isset($search->state) && $search->state != '') {
            $model = $model->where('state', $search->state);
        }
        //count
        if (isset($search->count) && $search->count != '') {
            $model = $model->where('count', $search->count);
        }
        //success_count
        if (isset($search->success_count) && $search->success_count != '') {
            $model = $model->where('success_count', $search->success_count);
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
        //导出者
        if (isset($search->created_by) && !empty($search->created_by)) {
            $createrIds = \Modules\Admin\Http\Models\User::where('name', 'like', '%'.$search->created_by.'%')->pluck(
                'id'
            );
            $model = $model->whereIn('created_by', $createrIds);
        }

        return $model;
    }

    /**
     * 状态文字获取器
     */
    public function getStateTextAttribute() {
        $text = '';
        if (isset($this->attributes['state'])) {
            switch ($this->attributes['state']) {
                case self::EXPORT_INIT:
                    $text = trans('lang.export_init');
                    break;
                case self::EXPORT_RUNNING:
                    $text = trans('lang.export_running');
                    break;
                case self::EXPORT_MERGING:
                    $text = trans('lang.export_merging');
                    break;
                case self::EXPORT_COMPLETE:
                    $text = trans('lang.export_complete');
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $text;
    }
}
