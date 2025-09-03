<?php
/**
 * NginxBanList.php UTF-8
 * nginx封禁记录
 *
 * @date    : 2025/1/10 15:16 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Models;
class NginxBanList extends Base {
    protected $table = 'nginx_ban_list';
    // 设置允许入库字段,数组形式
    protected $fillable = ['id', 'ban_type', 'ban_str', 'content', 'status', 'unban_time', 'service_type'];

    public static $statusList = [
        0 => '手动解封',
        1 => '正常封禁',
        2 => '程序解封',
    ];

    public $appends = ['status_str' , 'unban_time_str'];

    public function getStatusStrAttribute() {
        return self::$statusList[$this->status] ?? '';
    }

    public function getUnbanTimeStrAttribute() {
        return $this->unban_time ? date('Y-m-d H:i:s', $this->unban_time) : '';
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
                if (in_array($key, ['name', 'ban_str', 'remark'])) {
                    $model = $model->where($key, 'like', '%'.trim($value).'%');
                } else if (in_array($key, $timeArray)) {
                    if (is_array($value)) {
                        $model = $model->whereBetween($key, $value);
                    }
                } else if (is_array($value) && !in_array($key, $timeArray)) {
                    $model = $model->whereIn($key, $value);
                } elseif ($key == 'created_by' && !empty($value)) {
                    $userIds = \Modules\Admin\Http\Models\User::where('nickname', 'like', '%'.$value.'%')->pluck('id')
                                                              ->toArray();
                    $model = $model->whereIn('created_by', $userIds);
                } else {
                    $model = $model->where($key, $value);
                }
            }
        }

        return $model;
    }
}
