<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\User as AdminUser;

class SensitiveWords extends Base {
    // 设置允许入库字段,数组形式
    protected $fillable = ['word', 'status', 'sort'];

    /**
     * 处理查询列表条件数组
     *
     * @param $model  moxel
     * @param $search 搜索条件
     */
    public function HandleSearch($model, $search) {
        if (!is_array($search)) {
            $search = json_decode($search, true);
        }
        $search = array_filter($search, function ($v) {
            if (!(empty($v) && $v != "0")) {
                return true;
            }
        });
        if (!empty($search)) {
            $timeArray = ['created_at', 'updated_at'];
            foreach ($search as $key => $value) {
                if (in_array($key, ['word'])) {
                    $model = $model->where($key, 'like', '%'.trim($value).'%');
                } elseif (in_array($key, ['created_by', 'updated_by'])) {
                    if (!empty($value)) {
                        $user_id_list = AdminUser::query()->orWhere('name', 'like', '%'.trim($value).'%')
                                                 ->orWhere('nickname', 'like', '%'.trim($value).'%')
                                                 ->pluck('id')->toArray();
                        if ($key == 'updated_by') {
                            $model = $model->whereIn('updated_by', $user_id_list);
                        } else {
                            $model = $model->whereIn('created_by', $user_id_list);
                        }
                    }
                } else if (in_array($key, $timeArray)) {
                    if (is_array($value)) {
                        $model = $model->whereBetween($key, $value);
                    }
                } else if (is_array($value) && !in_array($key, $timeArray)) {
                    $model = $model->whereIn($key, $value);
                } else {
                    $model = $model->where($key, $value);
                }
            }
        }

        return $model;
    }
}
