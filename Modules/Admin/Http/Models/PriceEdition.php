<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
use Illuminate\Support\Facades\Redis;
class PriceEdition extends Base
{
    // 设置可以入库的字段
    protected $fillable = ['publisher_id', 'sort', 'status', 'created_by', 'updated_by',];

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['publisher'];
    public static $RedisKey = 'PriceEdition';
    /**
     * Register the model events for updating.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // 在创建成功后触发
        static::created(function ($model) {
            self::SaveToRedis($model);
        });

        // 在更新成功后触发
        static::updating(function ($model) {
            self::DeteleToRedis($model);
        });

        // 在删除成功后触发
        static::deleted(function ($mode) {
            self::DeteleToRedis($mode->id);
        });
    }

    /**
     * 出版商获取器
     */
    public function getPublisherAttribute()
    {
        $text = '';
        if (isset($this->attributes['publisher_id'])) {
            $publisherIds = explode(',', $this->attributes['publisher_id']);
            $text = Publisher::whereIn('id', $publisherIds)->pluck('name')->toArray();
            $text = implode(';', $text);
        }
        return $text;
    }



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

        //publisher_id 出版商
        if (isset($search->publisher_id) && !empty($search->publisher_id)) {
            $model = $model->whereRaw("FIND_IN_SET(?, publisher_id) > 0", [$search->publisher_id]);
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



        //查询外联表
        $model = $model->whereHas('priceEditionValueHasOne', function ($query) use ($search) {
            // 在这里添加条件
            if (isset($search->language_id) && !empty($search->language_id)) {
                $query->where('language_id', $search->language_id);
            }
        
            if (isset($search->rules) && !empty($search->rules)) {
                $query->where('rules', 'like', '%' . $search->rules . '%');
            }
            
            if (isset($search->is_logistics) && !empty($search->is_logistics)) {
                $query->where('is_logistics', $search->is_logistics);
            }

            if (isset($search->notice) && !empty($search->notice)) {
                $query->where('notice', 'like', '%' . $search->notice . '%');
            }

            if (isset($search->name) && !empty($search->name)) {
                $query->where('name', 'like', '%' . $search->name . '%');
            }
        });

        return $model;
    }


     // 定义与 PriceEditionValue 的关联
     public function priceEditionValueHasOne()
     {
         return $this->hasOne(PriceEditionValue::class, 'edition_id', 'id');
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
