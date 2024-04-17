<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User as Admin;

class Template extends Base {
    protected $table = 'template';
    protected $fillable
                     = [
            'name',            // 名称
            'type',           // 模版类型
            'btn_color',      // 按钮颜色
            'content',        // 模版内容
            'status',           // 状态
            'sort',             // 排序
            'created_by',       // 创建者
            'updated_by',       // 编辑者
        ];

    //模型关联
    public function tempCates() {
        return $this->belongsToMany(TemplateCategory::class, 'template_cate_mapping', 'temp_id', 'cate_id');
    }

    /**
     * 处理查询列表条件数组
     *
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request) {
        $search = json_decode($request->input('search'), true);
        if (!empty($search)) {
            $textField = ['name'];
            $numberField = ['id', 'sort', 'status'];
            $timeField = ['created_at', 'updated_at'];
            $userField = ['created_by', 'updated_by'];
            foreach ($search as $key => $value) {
                if (in_array($key, $textField) && $value != '') {
                    $model = $model->where($key, 'like', '%'.trim($value).'%');
                } else if (in_array($key, $numberField) && $value != '') {
                    $model = $model->where($key, $value);
                } else if (in_array($key, $timeField) && !empty($value) && count($value) > 0) {
                    $time = $value;
                    $model = $model->where($key, '>=', $time[0]);
                    $model = $model->where($key, '<=', $time[1]);
                } else if (in_array($key, $userField) && !empty($value)) {
                    $userIds = Admin::where('name', 'like', '%'.$value.'%')->pluck('id');
                    $userIds = $userIds ? $userIds : [];
                    $model = $model->whereIn($key, $userIds);
                }
            }
        }

        return $model;
    }
}
