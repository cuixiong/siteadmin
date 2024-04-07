<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
use Illuminate\Support\Facades\Redis;

class Dictionary extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'code', 'status', 'sort', 'remark', 'updated_by', 'created_by'];

    /**
     * 保存数据到Redis中
     */
    public static function SaveToRedis($redisKey, $data)
    {
        try {
            $data = is_array($data) ? $data : $data->toArray();
            $id = $data['id'];
            Redis::hset($redisKey, $id, json_encode($data));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 更新数据到Redis中
     */
    public static function UpdateToRedis($redisKey, $data)
    {
        try {
            $data = is_array($data) ? $data : $data->toArray();
            $id = $data['id'];
            // 先删除
            Redis::hdel($redisKey, $id);
            // 后新增
            Redis::hset($redisKey, $id, json_encode($data));
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
            return false;
        }
    }

    /**
     * 删除数据到Redis中
     */
    public static function DeteleToRedis($redisKey, $data)
    {
        try {
            $data = is_array($data) ? $data : $data->toArray();
            $id = $data['id'];
            Redis::hdel($redisKey, $id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
