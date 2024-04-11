<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
use Illuminate\Support\Facades\Redis;
use Stancl\Tenancy\Database\Models\Tenant;
use Illuminate\Support\Facades\DB;

class Dictionary extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'code', 'status', 'sort', 'remark', 'updated_by', 'created_by'];

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

        $dictionaryDataSql = [];
        $dictionaryValueDataSql = [];
        // 字典表
        $dictionaryTableName = (new Dictionary())->getTable();
        // 字典选项表
        $dictionaryValueTableName = (new DictionaryValue())->getTable();

        if ($type == self::SAVE_TYPE_FULL) {
            // 查询需要迁移的数据,并整理成sql语句
            // $dictionaryData = Dictionary::get()->each->setAppends([])->toArray();    
            // 因为有选择器转换了时间等字段,为了方便所以换一种查询方式
            $dictionaryData = DB::table($dictionaryTableName)->get()->toArray();
            foreach ($dictionaryData as $record) {
                $columns = [];
                $values = [];
                foreach ($record as $column => $value) {
                    $columns[] = "`$column`";
                    $values[] = "'$value'";
                }
                $dictionaryDataSql[] = "INSERT INTO $dictionaryTableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";
            }
            // $dictionaryValueData = DictionaryValue::get()->toArray();
            $dictionaryValueData = DB::table($dictionaryValueTableName)->get()->toArray();
            foreach ($dictionaryValueData as $record) {
                $columns = [];
                $values = [];
                foreach ($record as $column => $value) {
                    $columns[] = "`$column`";
                    $values[] = "'$value'";
                }
                $dictionaryValueDataSql[] = "INSERT INTO $dictionaryValueTableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";
            }
        } elseif ($type == self::SAVE_TYPE_SINGLE) {
            if (empty($id)) {
                return false;
            }
            // 查询需要迁移的数据
            $dictionaryData = DB::table($dictionaryTableName)->where(['id' => $id])->first();
            $dictionaryValueData = DB::table($dictionaryValueTableName)->where(['parent_id' => $id])->get()->toArray();
            // return $dictionaryValueData;
        }

        // return [$dictionaryData, $dictionaryValueData];
        // return [$dictionaryDataSql, $dictionaryValueDataSql];

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
            // return $currentTenant;
            // $currentTenant = tenancy()->tenant;
            // 在这里执行更新操作
            // DB::connection($currentTenant->getConnectionName())->table('your_table')->where('column', 'value')->update(['column' => 'new_value']);

            if ($type == self::SAVE_TYPE_FULL) {
                // 假定分站点已经有这个表了,这里我不建表
                // 复制数据到分站点数据库
                if (count($dictionaryDataSql) > 0) {
                    try {
                        DB::table($dictionaryTableName)->truncate(); // 清空目标表数据
                        DB::statement(implode('', $dictionaryDataSql));
                    } catch (\Throwable $th) {
                        return false;
                    }
                    // foreach ($dictionaryDataSql as $sql) {
                    //     try {
                    //         DB::statement($sql);
                    //     } catch (\Throwable $th) {
                    //         // return $sql;
                    //         return false;
                    //     }
                    // }
                }
                if (count($dictionaryValueDataSql) > 0) {
                    try {
                        DB::table($dictionaryValueTableName)->truncate(); // 清空目标表数据
                        DB::statement(implode('', $dictionaryValueDataSql));
                    } catch (\Throwable $th) {
                        return false;
                    }
                    // foreach ($dictionaryValueDataSql as $sql) {
                    //     try {
                    //         DB::statement($sql);
                    //     } catch (\Throwable $th) {
                    //         // return $sql;
                    //         return false;
                    //     }
                    // }
                }
            } elseif ($type == self::SAVE_TYPE_SINGLE) {
                // 处理字典表
                $dictionarySiteExist = DB::table($dictionaryTableName)->select('id')->where(['id' => $id])->value('id');
                if ($dictionarySiteExist) {
                    DB::table($dictionaryTableName)->where(['id' => $id])->update((array)$dictionaryData);
                } else {
                    DB::table($dictionaryTableName)->insert((array)$dictionaryData);
                }
                // 处理字典表
                $dictionaryValueExistIds = DB::table($dictionaryValueTableName)->select('id')->where(['parent_id' => $id])->pluck('id')->toArray() ?? [];
                // return $dictionaryValueExistIds;
                foreach ($dictionaryValueData as $record) {
                    $record = (array)$record;
                    if (in_array($record['id'], $dictionaryValueExistIds)) {
                        DB::table($dictionaryValueTableName)->where(['id' => $record['id']])->update($record);
                    } else {
                        DB::table($dictionaryValueTableName)->insert($record);
                    }
                }
                // return $dictionarySiteData;

            }
        }

        tenancy()->end();
        return true;
    }

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
