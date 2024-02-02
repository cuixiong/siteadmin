<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User as Admin;

class NewsCategory extends Base
{


    protected $table = 'news_category';

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name',             // 名称
        'url',              // 自定义链接
        'sort',             // 排序
        'status',           // 状态
        'created_by',       // 创建者
        'updated_by',       // 编辑者
        'seo_title',        // seo标题
        'seo_keyword',      // seo关键词
        'seo_description',  // seo描述


    ];

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request)
    {

        $search = json_decode($request->input('search'), true);


        if (!empty($search)) {
            $textField = ['name', 'url', 'seo_title', 'seo_keyword', 'seo_description'];
            $numberField = ['id', 'sort', 'status'];
            $timeField = ['created_at', 'updated_at'];
            $userField = ['created_by', 'updated_by'];
            foreach ($search as $key => $value) {
                if (in_array($key, $textField)  && $value != '') {
                    $model = $model->where($key, 'like', '%' . trim($value) . '%');
                } else if (in_array($key, $numberField) && $value != '') {
                    $model = $model->where($key, $value);
                } else if (in_array($key, $timeField) && !empty($value) && count($value) > 0) {
                    $time = $value;
                    $model = $model->where($key, '>=', $time[0]);
                    $model = $model->where($key, '<=', $time[1]);
                } else if (in_array($key, $userField) && !empty($value)) {

                    $userIds = Admin::where('name', 'like', '%' . $value . '%')->pluck('id');
                    $userIds = $userIds ? $userIds : [];
                    $model = $model->whereIn($key, $userIds);
                }
            }
        }
        return $model;
    }
}
