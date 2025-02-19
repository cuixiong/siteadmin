<?php

namespace Modules\Site\Http\Models;

use Illuminate\Support\Facades\DB;
use Modules\Site\Http\Models\Base;

class PostSubject extends Base
{
    protected $table = 'post_subject';

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name',
        'product_id',
        'product_category_id',
        'analyst',
        'version',
        'propagate_status',
        'last_propagate_time',
        'accepter',
        'accept_time',
        'accept_status',
        'status',
        'sort',
        'updated_by',
        'created_by'
    ];

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
    const CONDITION_EQUAL = 1;  
    const CONDITION_NOT_EQUAL = 2;   
    const CONDITION_TIME_BETWEEN = 3;   
    const CONDITION_TIME_NOT_BETWEEN = 4; 
    const CONDITION_CONTAIN = 5;    
    const CONDITION_NOT_CONTAIN = 6;   
    const CONDITION_IN = 7; 
    const CONDITION_NOT_IN = 8;  
    const CONDITION_EXISTS_IN = 9;
    const CONDITION_EXISTS_NOT_IN = 10;


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
            self::CONDITION_EXISTS_IN => [
                'name' => '符合其一'
            ],
            self::CONDITION_EXISTS_NOT_IN => [
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


    /**
     * 根据类型、传递条件添加筛选
     * @param string|array fields;
     * @param string|array content;
     */
    public static function getFiltersConditionQuery($query, $condition, $type, $fields, $content)
    {
        // 
        if ($type == self::ADVANCED_FILTERS_TYPE_TEXT && $condition == self::CONDITION_EQUAL) {
            // 文本-等于
            $query->where($fields, '=', $content);
        } elseif ($type == self::ADVANCED_FILTERS_TYPE_TEXT && $condition == self::CONDITION_NOT_EQUAL) {
            // 文本-不等于
            $query->where($fields, '<>', $content);
        } elseif ($type == self::ADVANCED_FILTERS_TYPE_TEXT && $condition == self::CONDITION_CONTAIN) {
            // 文本-包含(模糊查询)
            $query->where($fields, 'like', "%{$content}%");
        } elseif ($type == self::ADVANCED_FILTERS_TYPE_TEXT && $condition == self::CONDITION_NOT_CONTAIN) {
            // 文本-不包含(模糊查询)
            $query->where($fields, 'not like', "%{$content}%");
        } elseif ($type == self::ADVANCED_FILTERS_TYPE_DROPDOWNLIST && $condition == self::CONDITION_IN) {
            // 下拉-多选-任一
            $query->whereIn($fields, $content);
        } elseif ($type == self::ADVANCED_FILTERS_TYPE_DROPDOWNLIST && $condition == self::CONDITION_NOT_IN) {
            // 下拉-多选-排除
            $query->whereNotIn($fields, $content);
        } elseif ($type == self::ADVANCED_FILTERS_TYPE_TIME && $condition == self::CONDITION_TIME_BETWEEN) {
            // 时间-区间
            // 检查 content 是否为数组且有两个有效的值
            if (is_array($content) && count($content) === 2 && isset($content[0]) && isset($content[1])) {
                $query->whereBetween($fields, [$content[0], $content[1]]);
            } else {
                // 如果边界值有 null，改为单独的条件判断
                if (!empty($content[0])) {
                    $query->where($fields, '>=', $content[0]);
                }
                if (!empty($content[1])) {
                    $query->where($fields, '<=', $content[1]);
                }
            }
        } elseif ($type == self::ADVANCED_FILTERS_TYPE_TIME && $condition == self::CONDITION_TIME_NOT_BETWEEN) {
            // 时间-排除
            // 检查 content 是否为数组且有两个有效的值
            if (is_array($content) && count($content) === 2 && isset($content[0]) && isset($content[1])) {
                $query->whereNotBetween($fields, [$content[0], $content[1]]);
            } else {
                // 处理单边时间范围
                if (!empty($content[0])) {
                    // 如果只传开始时间，排除从该时间点开始的数据
                    $query->where($fields, '<', $content[0]);
                }
                if (!empty($content[1])) {
                    // 如果只传结束时间，排除截止时间之前的数据
                    $query->where($fields, '>', $content[1]);
                }
            }
        }elseif ($type == self::ADVANCED_FILTERS_TYPE_DROPDOWNLIST && $condition == self::CONDITION_EXISTS_IN) {
            // 关联子表-存在
            $query->whereExists(function ($query) use ($content){
                $query->select(DB::raw(1))
                      ->from('post_subject_link as psl')
                      ->whereRaw('psl.post_subject_id = ps.id')
                      ->whereIn('post_platform_id', $content);
            });
        } elseif ($type == self::ADVANCED_FILTERS_TYPE_DROPDOWNLIST && $condition == self::CONDITION_EXISTS_NOT_IN) {
            // 关联子表-不存在
            $query->whereNotExists(function ($query) use ($content){
                $query->select(DB::raw(1))
                      ->from('post_subject_link as psl')
                      ->whereRaw('psl.post_subject_id = ps.id')
                      ->whereIn('post_platform_id', $content);
            });
        } 

        return $query;
    }

    // 筛选
    public static function getFiltersQuery($query, $search, $requesterOwn = null)
    {
        $search = json_decode($search, true);
        if (!empty($search)) {
            $search = array_column($search, null, 'keyword');
        }else{
            return $query;
        }
        // return $search;
        /*
        * 数据筛选 开始
        */

        // id
        if (!empty($search['id']) && !empty($search['id']['content'])) {
            $condition = self::CONDITION_EQUAL;
            $searchItem = $search['id'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TEXT, 'ps.id', $searchItem['content']);
        }

        // 课题名称
        if (!empty($search['name']) && !empty($search['name']['content'])) {
            $condition = self::CONDITION_CONTAIN;
            $searchItem = $search['name'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TEXT, 'ps.name', $searchItem['content']);
            // return $query->dd();
        }
        // 行业
        if (!empty($searchModel['product_category_id']) && isset($searchModel['product_category_id']['content']) && count($searchModel['product_category_id']['content']) > 0) {
            $condition = self::CONDITION_IN;
            $searchItem = $searchModel['product_category_id'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, 'ps.product_category_id', $searchItem['content']);
        }

        // 分析师
        if (!empty($search['analyst']) && !empty($search['analyst']['content'])) {
            $condition = self::CONDITION_CONTAIN;
            $searchItem = $search['analyst'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TEXT, 'ps.analyst', $searchItem['content']);
        }

        // 版本
        if (!empty($search['version']) && !empty($search['version']['content'])) {
            $condition = self::CONDITION_CONTAIN;
            $searchItem = $search['version'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TEXT, 'ps.version', $searchItem['content']);
        }

        // 状态
        if (!empty($search['status']) && !empty($search['status']['content'])) {
            $condition = self::CONDITION_EQUAL;
            $searchItem = $search['status'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TEXT, 'ps.status', $searchItem['content']);
        }

        // 创建时间
        if (!empty($search['created_at']) && !empty($search['created_at']['content'])) {
            $condition = self::CONDITION_TIME_BETWEEN;
            $searchItem = $search['created_at'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TIME, 'ps.created_at', $searchItem['content']);
        }

        // 修改时间
        if (!empty($searchModel['updated_at']) && isset($searchModel['updated_at']['content'])) {
            $condition = self::CONDITION_TIME_BETWEEN;
            $searchItem = $search['updated_at'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TIME, 'ps.updated_at', $searchItem['content']);
        }

        
        // 宣传状态
        if (!empty($search['propagate_status']) && !empty($search['propagate_status']['content'])) {
            $condition = self::CONDITION_EQUAL;
            $searchItem = $search['propagate_status'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TEXT, 'ps.propagate_status', $searchItem['content']);
        }

        // 最后宣传时间
        if (!empty($searchModel['propagate_time']) && isset($searchModel['propagate_time']['content'])) {
            $condition = self::CONDITION_TIME_BETWEEN;
            $searchItem = $search['propagate_time'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TIME, 'ps.propagate_time', $searchItem['content']);
        }
        
        // 领取人
        if (!empty($searchModel['accepter']) && isset($searchModel['accepter']['content']) && count($searchModel['accepter']['content']) > 0) {
            $condition = self::CONDITION_IN;
            $searchItem = $searchModel['accepter'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, 'ps.accepter', $searchItem['content']);
        }

        // 领取状态
        if (!empty($search['accept_status']) && !empty($search['accept_status']['content'])) {
            $condition = self::CONDITION_EQUAL;
            $searchItem = $search['accept_status'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TEXT, 'ps.accept_status', $searchItem['content']);
        }

        // 领取时间
        if (!empty($searchModel['accept_time']) && isset($searchModel['accept_time']['content'])) {
            $condition = self::CONDITION_TIME_BETWEEN;
            $searchItem = $search['accept_time'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_TIME, 'ps.accept_time', $searchItem['content']);
        }

        // 平台-子查询
        if (!empty($search['post_platform_id']) && isset($search['post_platform_id']['content']) && count($search['post_platform_id']['content']) > 0) {

            $condition = self::CONDITION_EXISTS_IN;
            $searchItem = $search['post_platform_id'];
            if (isset($searchItem['condition'])) {
                $condition = $searchItem['condition'];
            }
            $query = self::getFiltersConditionQuery($query, $condition, self::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, 'ps.post_platform_id', $searchItem['content']);

            // return $query->createCommand()->getRawSql();
        }


        // //创建者
        // if (!empty($searchModel['created_by']) && isset($searchModel['created_by']['content'])) {
        //     $condition = "in";
        //     $searchItem = $searchModel['created_by'];
        //     if (isset($searchItem['condition'])) {
        //         $condition = $conditionArray[$searchItem['condition']];
        //     }
        //     $query->andFilterWhere([$condition, 's.created_by', $searchItem['content']]);
        // }


        // //修改者
        // if (!empty($searchModel['updated_by']) && isset($searchModel['updated_by']['content'])) {
        //     $condition = "in";
        //     $searchItem = $searchModel['updated_by'];
        //     if (isset($searchItem['condition'])) {
        //         $condition = $conditionArray[$searchItem['condition']];
        //     }
        //     $query->andFilterWhere([$condition, 's.updated_by', $searchItem['content']]);
        // }




        // //链接
        // if (!empty($searchModel['url']) && isset($searchModel['url']['content']) && $searchModel['url']['content'] != '') {
        //     $condition = $conditionArray[self::CONDITION_EXISTS_CONTAIN];
        //     $searchItem = $searchModel['url'];
        //     if (isset($searchItem['condition'])) {
        //         $condition = $conditionArray[$searchItem['condition']];
        //     }
        //     // return $condition;
        //     $query->andFilterWhere([
        //         $condition,
        //         (new yii\db\Query())
        //             ->select('psv.subject_id')
        //             ->distinct()
        //             ->from(PostSubjectVersion::tableName() . ' as psv')
        //             ->leftJoin(['pu' => PostUrl::tableName()], 'pu.post_subject_id = psv.id')
        //             ->andWhere('s.id = psv.subject_id')
        //             ->andWhere(['like', 'pu.url', $searchItem['content']])
        //     ]);
        //     // return $query->createCommand()->getRawSql();
        // }


        // //领取人-查看个人
        // if (!empty($requesterOwn)) {

        //     $searchItem = $searchModel['request_id'];
        //     $query->andFilterWhere([
        //         'exists',
        //         (new yii\db\Query())
        //             ->select('psv.subject_id')
        //             ->distinct()
        //             ->from(PostSubjectVersion::tableName() . ' as psv')
        //             ->leftJoin(PostVersion::tableName() . ' as pv', 'psv.version_id = pv.id')
        //             ->andWhere('s.id = psv.subject_id')
        //             ->andWhere(['pv.status' => PostVersion::STATUS_ACTIVE, 'psv.request_id' => $requesterOwn])
        //     ]);
        //     // return $query->createCommand()->getRawSql();
        // }




        // return $query->createCommand()->getRawSql();
        // return $query->asArray()->all();
        // return $query->count();
        // echo $query->createCommand()->getRawSql();exit;
        // 数据筛选 结束
        return $query;
    }
}
