<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class Country extends Base
{
    protected $table = 'countrys';

    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'data', 'provinces', 'acronym', 'code', 'status', 'sort', 'updated_by', 'created_by'];


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
    public static function getCountryName($country_id, $language)
    {

        $data = Country::where('status', 1)->where('id', $country_id)->value('data');
        $name = '';
        if ($data) {
            $data = json_decode($data, true);
            $language = request()->HeaderLanguage ?? '';
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
     * 获取某个省份城市
     */
    public static function getCityName($city_id)
    {
        $name = '';
        //44代表国内
        // $data = Country::where('status', 1)->where('id', 44)->value('provinces');
        // if ($data) {
        //     $data = json_decode($data, true);
        //     $data = array_column($data, null, 'id');
        //     return (isset($data[$city_id]) && isset($data[$city_id]['status']) && $data[$city_id]['status'] == 1) ? $data[$city_id]['name'] : '';
        // }
        return $name;
    }
}
