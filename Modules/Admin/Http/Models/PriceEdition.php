<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class PriceEdition extends Base
{
    // 设置可以入库的字段
    protected $fillable = ['publisher_id', 'sort', 'status', 'created_by', 'updated_by',];

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['publisher'];
    public static $RedisKey = 'PriceEdition';

    const SAVE_TYPE_FULL = 1;   // 全量同步
    const SAVE_TYPE_SINGLE = 2; // 单条同步

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
     * 保存数据到多个分站点中
     * @param $type 整个表更新或者更新单条数据
     * @param $id 要修改数据的标识
     * @param $isAllSite 是否同步所有站点
     * @param $siteIds 站点id
     * 
     */
    public static function SaveToSite($type = self::SAVE_TYPE_FULL, $id = null, $isAllSite = false, $siteIds = null)
    {
        $site = null;
        if ($isAllSite) {
            $site = Site::where(['status' => 1])->get()->toArray();
        } elseif (!empty($siteIds)) {

            $siteIds = explode(',', $siteIds);
            $site = Site::where(['status' => 1])->whereIn(['id' => $siteIds])->get()->toArray();
        }
        if (!$site) {
            return false;
        }

        $priceEditionDataSql = [];
        $priceEditionValueDataSql = [];
        // 价格版本表
        $priceEditionTableName = (new PriceEdition())->getTable();
        // 价格版本项表
        $priceEditionValueTableName = (new PriceEditionValue())->getTable();

        // 查询需要迁移的数据
        $priceEditionData = DB::table($priceEditionTableName)->get()->toArray();
        $priceEditionValueData = DB::table($priceEditionValueTableName)->orderBy('sort', 'asc')->orderBy('id', 'asc')->get()->toArray();
        // 整理每个出版商对应的价格版本数组 开始
        // key为版本id的价格版本数组，格式为 ['25'=>[['id'=>1,'name'=>'pdf版',...],['id'=>2,'name'=>'PDF+纸质版',...]]]
        $priceEditionValueByEditionId = [];
        foreach ($priceEditionValueData as $key => $item) {
            $item = (array)$item;
            if (!isset($priceEditionValueByEditionId[$item['edition_id']])) {
                $priceEditionValueByEditionId[$item['edition_id']] = [];
            }
            $priceEditionValueByEditionId[$item['edition_id']][] = $item;
        }
        // return $priceEditionValueByEditionId;
        $publisherPriceEditionData = [];
        foreach ($priceEditionData as $key => $edition) {
            $edition = (array)$edition;
            $publishers = explode(',', $edition['publisher_id'] ?? '');
            if (count($publishers) > 0) {
                foreach ($publishers as $key => $publisher) {
                    if (!isset($priceEditionValueByEditionId[$edition['id']])) {
                        continue;
                    }
                    if (!isset($publisherPriceEditionData[$publisher])) {
                        $publisherPriceEditionData[$publisher] = [];
                    }
                    $publisherPriceEditionData[$publisher] = array_merge($publisherPriceEditionData[$publisher], $priceEditionValueByEditionId[$edition['id']]);
                }
            } else {
                continue;
            }
        }
        // return $publisherPriceEditionData;
        // 整理每个出版商对应的价格版本数组 结束

        // 开始同步操作
        foreach ($site as $siteItem) {

            // 获取当前租户信息,取消上一个租户的连接
            $currentTenant = tenancy()->tenant;
            if ($currentTenant) {
                // 如果当前处于租户则切换回中央数据库
                tenancy()->end();
            }

            // 设置当前租户
            tenancy()->initialize($siteItem['name']);
            // 获取当前租户信息
            $currentTenant = tenancy()->tenant;

            if ($type == self::SAVE_TYPE_FULL) {
                // 假定分站点已经有这个表了,这里我不建表
                $publishers = explode(',', $siteItem['publisher_id'] ?? '');

                // 处理价格版本数据，整理为sql语句
                foreach ($priceEditionData as $record) {
                    $record = (array)$record;
                    $originPublishers = explode(',', $record['publisher_id'] ?? '');
                    $commonPublisher = array_intersect($originPublishers, $publishers);
                    if ($commonPublisher && count($commonPublisher) > 0) {

                        $columns = [];
                        $values = [];
                        foreach ($record as $column => $value) {
                            $columns[] = "`$column`";
                            if ($value === null) {
                                $values[] = "null";
                            } elseif (is_numeric($value)) {
                                $values[] = $value;
                            } else {
                                $values[] = "'$value'";
                            }
                        }
                        $priceEditionDataSql[] = "INSERT INTO $priceEditionTableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";
                    }
                }

                // 处理价格版本项数据，整理为sql语句
                $tempValueData = [];
                foreach ($publishers as $publisher) {
                    if (empty($publisher)) {
                        continue;
                    }
                    $tempValueData = array_merge($tempValueData, $publisherPriceEditionData[$publisher]);
                }
                foreach ($tempValueData as $record) {

                    // id特殊处理
                    if (isset($record['bind_id']) && !empty($record['bind_id'])) {
                        $record['id'] = $record['bind_id'];
                        unset($record['bind_id']);
                    } else {
                        continue;
                    }
                    $columns = [];
                    $values = [];
                    foreach ($record as $column => $value) {
                        $columns[] = "`$column`";
                        if ($value === null) {
                            $values[] = "null";
                        } elseif (is_numeric($value)) {
                            $values[] = $value;
                        } else {
                            $values[] = "'$value'";
                        }
                    }
                    $priceEditionValueDataSql[] = "INSERT INTO $priceEditionValueTableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";
                }
                // 复制数据到分站点数据库
                if (count($priceEditionDataSql) > 0) {
                    DB::table($priceEditionTableName)->truncate(); // 清空目标表数据
                    foreach ($priceEditionDataSql as $sql) {
                        try {
                            DB::statement($sql);
                        } catch (\Throwable $th) {
                            // return $sql;
                            return false;
                        }
                    }
                }
                if (count($priceEditionValueDataSql) > 0) {
                    // try {
                    DB::table($priceEditionValueTableName)->truncate(); // 清空目标表数据
                    //     DB::statement(implode('', $priceEditionValueDataSql));
                    // } catch (\Throwable $th) {
                    //     return $th->getMessage();
                    //     return false;
                    // }
                    foreach ($priceEditionValueDataSql as $sql) {
                        try {
                            DB::statement($sql);
                        } catch (\Throwable $th) {
                            // return $sql;
                            return false;
                        }
                    }
                }
            } elseif ($type == self::SAVE_TYPE_SINGLE) {
                // 不好处理，只好直接全量更新数据了

            }
        }

        tenancy()->end();
        return true;
    }

    /**
     * 保存数据到Redis中
     */
    public static function SaveToRedis($data)
    {
        try {
            $data = is_array($data) ? $data : $data->toArray();
            $id = $data['id'];
            Redis::hset(self::$RedisKey, $id, json_encode($data));
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
            Redis::hdel(self::$RedisKey, $id);
            // 后新增
            Redis::hset(self::$RedisKey, $id, json_encode($data));
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
            Redis::hdel(self::$RedisKey, $id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
