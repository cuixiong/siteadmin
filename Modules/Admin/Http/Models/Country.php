<?php

namespace Modules\Admin\Http\Models;

use App\Const\NotityTypeConst;
use App\Const\QueueConst;
use App\Jobs\NotifySite;
use Modules\Admin\Http\Models\Base;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class Country extends Base
{
    // redis KEY名
    protected static $RedisKey = 'Country';

    protected $table = 'countrys';

    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'data', 'acronym', 'code', 'status', 'sort', 'updated_by', 'created_by'];


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

        //acronym
        if (isset($search->acronym) && !empty($search->acronym)) {
            $model = $model->where('acronym', 'like', '%' . $search->acronym . '%');
        }

        //code
        if (isset($search->code) && !empty($search->code)) {
            $model = $model->where('code', 'like', '%' . $search->code . '%');
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
        return $model;
    }


    /**
     * 获取某个国家的对应语言的名称
     */
    public static function getCountryName($country_id, $language = null)
    {

        $data = Country::where('status', 1)->where('id', $country_id)->value('data');
        $name = '';
        if ($data) {
            $data = json_decode($data, true);
            if (!$language) {
                $language = request()->HeaderLanguage ?? '';
            }
            $sitename = getSiteName();
            if(in_array($sitename , ['mrrs' , 'yhen' , 'qyen', 'lpien' , 'mmgen' , 'giren'])){
                $language = 'en';
            }
            switch ($language) {
                case 'en':
                    $name = $data['en'];
                    break;

                case 'zh':
                    $name = $data['zh-cn'];
                    break;

                case 'jp':
                    $name = $data['jp'];
                    break;

                default:
                    $name = $data['en'];
                    break;
            };
        }
        return $name;
    }

    /**
     * 获取对应语言的国家列表
     */
    public static function getCountryList($language = null)
    {
        $countryData = Country::where('status', 1)->select(['id', 'data'])->orderBy('sort', 'asc')->orderBy('id', 'desc')->get()->toArray();

        $data = [];
        if ($countryData) {
            if (!$language) {
                $language = request()->HeaderLanguage ?? '';
            }
            foreach ($countryData as $key => $item) {
                $country = json_decode($item['data'], true);
                switch ($language) {
                    case 'en':
                        $name = $country['en'];
                        break;

                    case 'zh':
                        $name = $country['zh-cn'];
                        break;

                    case 'jp':
                        $name = $country['jp'];
                        break;

                    default:
                        $name = $country['en'];
                        break;
                };
                $data[] = ['label' => $name, 'value' => $item['id']];
            }
        }
        return $data;
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
        return true;
        //同步分站点不在总控直接同步 , 采用异步延时通知的方式
        // TODO: cuizhixiong 2024/9/12 待完善
        syncSiteDbByType(NotityTypeConst::SYNC_SITE_COUNTRY);

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

        // 国家
        $countryTableName = (new Country())->getTable();

        if ($type == self::SAVE_TYPE_FULL) {
            // 查询需要迁移的数据
            // 因为有选择器转换了时间等字段,为了方便所以换一种查询方式
            $countryData = DB::table($countryTableName)->get()->toArray();
        } elseif ($type == self::SAVE_TYPE_SINGLE) {
            if (empty($id)) {
                return false;
            }
            // 查询需要迁移的数据
            $countryData = DB::table($countryTableName)->where(['id' => $id])->first();
            // return $countryValueData;
        }

        // return $countryData;
        // return $countryDataSql;

        foreach ($site as $siteItem) {
            $language_code = Language::where('id', $siteItem['language_id'])->value('code');
            $language = '';
            switch ($language_code) {
                case 'en':
                    $language = 'en';
                    break;

                case 'cn':
                    $language = 'zh-cn';
                    break;

                case 'jp':
                    $language = 'jp';
                    break;

                default:
                    $language = $language_code;
                    break;
            };
            // return $language;
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

                // 根据语言调整数据
                $countryDataSql = [];
                foreach ($countryData as $aaa=>$record) {
                    $record = (array)$record;
                    // 含有单引号的处理
                    if (isset($record['name']) && !empty($record['name'])) {
                        $record['name'] = addslashes($record['name']);
                    }
                    // 含有单引号的处理,而且取对应网站的语言
                    if (isset($record['data']) && !empty($record['data'])) {
                        $record['data'] = json_decode($record['data'], true);
                        $record['data'] = addslashes($record['data'][$language] ?? $record['name']);
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
                        // $tempValues = "'$value'";
                        // if (in_array($column, ['updated_at','created_at','updated_by','created_by',])) {
                        // }
                    }
                    $countryDataSql[] = "INSERT INTO $countryTableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";
                    // return [$record,$countryDataSql];
                }

                // 复制数据到分站点数据库
                if (count($countryDataSql) > 0) {
                    DB::table($countryTableName)->truncate(); // 清空目标表数据
                    foreach ($countryDataSql as $key => $sql) {
                        // return $sql;

                        try {
                            DB::statement($sql);
                        } catch (\Throwable $th) {
                            // return $sql;
                            // return $th->getMessage();
                            return false;
                        }
                    }
                    // return implode('', $countryDataSql);
                    // return DB::statement(implode('', $countryDataSql));
                }
            } elseif ($type == self::SAVE_TYPE_SINGLE) {

                // 处理国家表
                // 总控有数据则进行增改操作
                if ($countryData) {
                    $countryData = (array)$countryData;
                    // 含有单引号的处理
                    if (isset($countryData['name']) && !empty($countryData['name'])) {
                        $countryData['name'] = addslashes($countryData['name']);
                    }
                    // 含有单引号的处理,而且取对应网站的语言
                    if (isset($countryData['data']) && !empty($countryData['data'])) {
                        $countryData['data'] = json_decode($countryData['data'], true);
                        $countryData['data'] = addslashes($countryData['data'][$language] ?? $countryData['name']);
                    }

                    $countrySiteExist = DB::table($countryTableName)->select('id')->where(['id' => $id])->value('id');
                    if ($countrySiteExist) {
                        DB::table($countryTableName)->where(['id' => $id])->update($countryData);
                    } else {
                        DB::table($countryTableName)->insert($countryData);
                    }
                } else {
                    // 总控没有数据则进行删除操作
                    DB::table($countryTableName)->delete($id);
                }

                // return $countrySiteData;

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
}
