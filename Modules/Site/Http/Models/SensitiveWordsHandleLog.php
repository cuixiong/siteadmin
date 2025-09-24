<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\User as AdminUser;

class SensitiveWordsHandleLog extends Base {

    protected $table = 'sensitive_words_handle_log';
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'log_type',
        'words', 
        'old_words', 
        'product_hidden_count',
        'product_show_count',
        'subject_delete_count',
        'product_hidden_details',
        'product_show_details',
        'subject_delete_details',
    ];

    const SENSITIVE_WORDS_STORE = 1;
    const SENSITIVE_WORDS_UPDATE = 2;
    const SENSITIVE_WORDS_CHANGE_STATUS = 3;
    const SENSITIVE_WORDS_DESTORY = 4;
    const SENSITIVE_WORDS_BATCH = 5;

    public static function getLogTypeList(){
        return [
            self::SENSITIVE_WORDS_STORE => '新增',
            self::SENSITIVE_WORDS_UPDATE => '修改',
            self::SENSITIVE_WORDS_CHANGE_STATUS => '状态开关',
            self::SENSITIVE_WORDS_DESTORY => '删除',
            self::SENSITIVE_WORDS_BATCH => '批量',
        ];
    }

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
