<?php

namespace Modules\Site\Http\Models;
use Modules\Admin\Http\Models\User as Admin;
use Modules\Site\Http\Models\Base;
class Partner extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name', 'logo', 'type', 'status','sort', 'updated_by', 'created_by'];


    protected $appends = ['typeText'];

    public function getTypeTextAttribute(){
        $type = [
            '0' => '全部',
            '1' => '首页',
            '2' => '其他',
        ];
        return $type[$this->attributes['type']];
    }

    // logo修改器
    public function setLogoAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['logo'] = $value;
        return $value;
    }
    // logo获取器
    public function getLogoAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }


    /**
     * 处理查询列表条件数组
     * @param $model moxel
     * @param $search 搜索条件
     */
    public function HandleSearch($model,$search){
        if(!is_array($search)){
            $search = json_decode($search,true);
        }
        $search = array_filter($search,function($v){
            if(!(empty($v) && $v != "0")){
                return true;
            }
        });
        if(!empty($search)){
            $timeArray = ['created_at','updated_at'];
            foreach ($search as $key => $value) {
                if(in_array($key,['name','english_name','title'])){
                    $model = $model->where($key,'like','%'.trim($value).'%');
                } else if (in_array($key,$timeArray)){
                    if(is_array($value)){
                        $model = $model->whereBetween($key,$value);
                    }
                } else if (in_array($key,['type'])){
                    if(!empty($value )){
                        $model = $model->where($key,$value);
                    }
                } else if(is_array($value) && !in_array($key,$timeArray)){
                    $model = $model->whereIn($key,$value);
                } else if (in_array($key, ['created_by','updated_by']) && !empty($value)) {
                    $userIds = Admin::where('nickname', 'like', '%'.$value.'%')->pluck('id');
                    $userIds = $userIds ? $userIds : [];
                    $model = $model->whereIn($key, $userIds);
                } else {
                    $model = $model->where($key,$value);
                }
            }
        }
        return $model;
    }


}
