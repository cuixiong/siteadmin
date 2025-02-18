<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class PostSubject extends Base
{
    protected $table = 'post_subject';

    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'product_id', 'product_category_id', 'analyst', 'version', 'propagate_status', 'last_propagate_time', 'accepter','accept_time', 
    'accept_status', 'status', 'sort', 'updated_by', 'created_by'];

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
        //id 
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }

        //name
        if (isset($search->name) && !empty($search->name)) {
            $model = $model->where('name', 'like', '%' . $search->name . '%');
        }

        //product_category_id
        if (isset($search->product_category_id) && !empty($search->product_category_id)) {
            $model = $model->where('product_category_id', $search->product_category_id);
        }
        //analyst
        if (isset($search->analyst) && !empty($search->analyst)) {
            $model = $model->where('analyst', 'like', '%' . $search->analyst . '%');
        }
        // propagate_status 宣传状态
        if (isset($search->propagate_status) && !empty($search->propagate_status)) {
            $model = $model->where('propagate_status', $search->propagate_status);
        }

        // accepter 领取者
        if (isset($search->accepter) && !empty($search->accepter)) {
            $model = $model->where('accepter', $search->accepter);
        }
        // accept_status 领取状态
        if (isset($search->accept_status) && $search->accept_status != '') {
            $model = $model->where('accept_status ', $search->accept_status);
        }


        //sort
        if (isset($search->sort) && !empty($search->sort)) {
            $model = $model->where('sort', $search->sort);
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

        // 领取时间
        if (isset($search->accept_time) && !empty($search->accept_time)) {
            $acceptTime = $search->accept_time;
            $model = $model->where('accept_time', '>=', $acceptTime[0]);
            $model = $model->where('accept_time', '<=', $acceptTime[1]);
        }

        //最后宣传时间
        if (isset($search->last_propagate_time) && !empty($search->last_propagate_time)) {
            $lastTime = $search->last_propagate_time;
            $model = $model->where('last_time', '>=', $lastTime[0]);
            $model = $model->where('last_time', '<=', $lastTime[1]);
        }
        return $model;
    }

    
    //前端高级筛选不同控件类型
    const ADVANCED_FILTERS_TYPE_TEXT = 1; //普通输入框
    const ADVANCED_FILTERS_TYPE_DROPDOWNLIST = 2; //下拉框
    const ADVANCED_FILTERS_TYPE_TIME = 3; //时间选择
    
    //组合条件
    const CONDITION_EQUAL = 1;   // 等于 = 
    const CONDITION_NOT_EQUAL = 2;   // 不等于 <>或!=
    const CONDITION_TIME_BETWEEN = 3;   // 时间-在区间内 
    const CONDITION_TIME_NOT_BETWEEN = 4; // 时间-不在区间内
    const CONDITION_CONTAIN = 5;    // 文字包含,like
    const CONDITION_NOT_CONTAIN = 6;    // 文字不包含,not like
    const CONDITION_IN = 7;    // 包含,in
    const CONDITION_NOT_IN = 8;    // 不包含,not in

    
    /**
     * 目录列表高级筛选-组合条件
     */
    public static function getFiltersCondition(...$indexs)
    {
        $data =  [
            self::CONDITION_EQUAL => [
                'name' => '等于'
            ],
            self::CONDITION_NOT_EQUAL => [
                'name' => '不等于'
            ],
            self::CONDITION_TIME_BETWEEN => [
                'name' => '等于'
            ],
            self::CONDITION_TIME_NOT_BETWEEN => [
                'name' => '不等于'
            ],
            self::CONDITION_CONTAIN => [
                'name' => '包含'
            ],
            self::CONDITION_NOT_CONTAIN => [
                'name' => '不包含'
            ],
            self::CONDITION_IN => [
                'name' => '符合其一'
            ],
            self::CONDITION_NOT_IN => [
                'name' => '除此之外'
            ],
        ];
        //添加id
        $data = array_map(function ($key, $item) {
            $item['id'] = $key;
            return $item;
        },  array_keys($data), $data);
        $data = array_column($data, null, 'id');
        $result = [];
        if (!empty($indexs)) {
            foreach ($indexs as $index) {
                array_push($result, $data[$index]);
            }
        } else {
            $result = $data;
        }
        return array_values($result);
    }
}
