<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
use Illuminate\Support\Facades\DB;

class Language extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'code', 'status', 'sort', 'updated_by', 'created_by'];
    public static $RedisKey = 'Languages';

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

        //order
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
        return $model;
    }

    
    const SAVE_TYPE_FULL = 1;   // 全量同步
    const SAVE_TYPE_SINGLE = 2; // 单条同步
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

        $languageDataSql = [];
        // 语言表
        $languageTableName = (new Language())->getTable();

        if ($type == self::SAVE_TYPE_FULL) {
            // 查询需要迁移的数据,并整理成sql语句
            // $languageData = Language::get()->each->setAppends([])->toArray();    
            // 因为有选择器转换了时间等字段,为了方便所以换一种查询方式
            $languageData = DB::table($languageTableName)->get()->toArray();
            foreach ($languageData as $record) {
                $columns = [];
                $values = [];
                foreach ($record as $column => $value) {
                    $columns[] = "`$column`";
                    if ($value == null) {
                        $values[] = "null";
                    } elseif (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        $values[] = "'$value'";
                    }
                }
                $languageDataSql[] = "INSERT INTO $languageTableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";
            }
        } elseif ($type == self::SAVE_TYPE_SINGLE) {
            if (empty($id)) {
                return false;
            }
            // 查询需要迁移的数据
            $languageData = DB::table($languageTableName)->where(['id' => $id])->first();
            // return $languageValueData;
        }

        // return [$languageData, $languageValueData];
        // return [$languageDataSql, $languageValueDataSql];

        foreach ($site as $siteItem) {

            // if(!isset($siteItem['database_id'])){
            //     continue;
            // }

            // //获取数据库配置
            // $database = Database::find($site['database_id']);

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
                // 复制数据到分站点数据库
                if (count($languageDataSql) > 0) {
                    // try {
                        DB::table($languageTableName)->truncate(); // 清空目标表数据
                    //     DB::statement(implode('', $languageDataSql));
                    // } catch (\Throwable $th) {
                    //     return false;
                    // }
                    foreach ($languageDataSql as $sql) {
                        try {
                            DB::statement($sql);
                        } catch (\Throwable $th) {
                            return $sql;
                            return false;
                        }
                    }
                }
            } elseif ($type == self::SAVE_TYPE_SINGLE) {

                // 处理字典表
                // 总控有数据则进行增改操作
                if ($languageData) {
                    $languageSiteExist = DB::table($languageTableName)->select('id')->where(['id' => $id])->value('id');
                    if ($languageSiteExist) {
                        DB::table($languageTableName)->where(['id' => $id])->update((array)$languageData);
                    } else {
                        DB::table($languageTableName)->insert((array)$languageData);
                    }
                } else {
                    // 总控没有数据则进行删除操作
                    DB::table($languageTableName)->delete($id);
                }

                // return $languageSiteData;

            }
        }

        tenancy()->end();
        return true;
    }
}
