<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
use Illuminate\Support\Facades\Redis;

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
