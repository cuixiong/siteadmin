<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User as Admin;

class Template extends Base {
    protected $table = 'template';
    
    protected $fillable = [
        'name',
        'type',
        'is_auto_post',
        'btn_color',
        'content',
        'status',
        'sort',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at'
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
            $numberField = ['id', 'sort', 'status', 'btn_color','is_auto_post'];
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
                    $userIds = Admin::where('nickname', 'like', '%'.$value.'%')->pluck('id')->toArray();
                    $userIds = $userIds ? $userIds : [];
                    $model = $model->whereIn($key, $userIds);
                }
            }
        }
        //区分是内容模板,还是标题模版
        $type = $request->input('type') ?? 1;
        $model->where("type", $type);
        //分类查询
        if (!empty($search['cate_id'])) {
            $tcmModel = new TemplateCateMapping();
            $template_id_list = $tcmModel->where("cate_id", $search['cate_id'])->pluck("temp_id")->toArray();
            $model->whereIn("id", $template_id_list);
        }

        return $model;
    }
}
