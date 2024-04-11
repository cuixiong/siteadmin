<?php

namespace Modules\Admin\Http\Models;

use Illuminate\Support\Facades\Redis;
use Modules\Admin\Http\Models\Base;

class PriceEditionValue extends Base
{
    // redis KEY名
    protected static $RedisKey = 'PriceEditionValue';
    // 设置可以入库的字段
    protected $fillable = ['name', 'edition_id', 'language_id', 'rules', 'notice', 'sort', 'status', 'is_logistics', 'created_by', 'updated_by',];

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['language', 'logistics'];

    /**
     * Register the model events for updating.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // // 在创建成功后触发
        // static::created(function ($model) {
        //     self::SaveToRedis($model);
        // });

        // // 在更新成功后触发
        // static::updating(function ($model) {
        //     self::DeteleToRedis($model);
        // });

        // // 在删除成功后触发
        // static::deleted(function ($mode) {
        //     self::DeteleToRedis($mode->id);
        // });
    }

    /**
     * 语言获取器
     */
    public function getLanguageAttribute()
    {
        $text = '';
        if (isset($this->attributes['language_id'])) {
            $text = Language::where('id', $this->attributes['language_id'])->value('name');
        }
        return $text;
    }

    /**
     * 物流获取器
     */
    public function getLogisticsAttribute()
    {
        $text = '';
        if (isset($this->attributes['is_logistics'])) {
            $logisticsTxtArray = array_column(SelectTxt::GetLogisticsTxt(), 'name', 'id');
            $text = $logisticsTxtArray[$this->attributes['is_logistics']] ?? '';
        }
        return $text;
    }

    /**
     * 保存数据到Redis中
     */
    public static function SaveToRedis($data)
    {
        try {
            $data = is_array($data) ? $data : $data->toArray();
            $id = $data['id'];
            Redis::hset(self::$RedisKey,$id,json_encode($data));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 更新数据到Redis中
     */
    public static function UpdateToRedis($data)
    {
        try {
            $data = is_array($data) ? $data : $data->toArray();
            $id = $data['id'];
            // 先删除
            Redis::hdel(self::$RedisKey,$id);
            // 后新增
            Redis::hset(self::$RedisKey,$id,json_encode($data));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 删除数据到Redis中
     */
    public static function DeteleToRedis($data)
    {
        try {
            $data = is_array($data) ? $data : $data->toArray();
            $id = $data['id'];
            Redis::hdel(self::$RedisKey,$id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
