<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\Country;

class User extends Base {
    // 设置允许入库字段,数组形式
    protected $fillable
        = ['name', 'username', 'email', 'phone', 'area_id', 'status', 'company', 'department','check_email', 'login_time',
           'updated_by', 'created_by' , 'password' , 'province_id' , 'city_id'];
    // 添加虚拟字段
    protected $appends = ['area_name', 'login_time'];

    public function getAreaNameAttribute() {
        if (isset($this->attributes['area_id'])) {
            $value = Country::where('status', 1)->where('id', $this->attributes['area_id'])->value('name');
            $value = $value ? $value : "";
        } else {
            $value = "";
        }

        return $value;
    }

    public function getLoginTimeAttribute() {
        if (!empty($this->attributes['login_time'])) {
            return date("Y-m-d", $this->attributes['login_time']);
        } else {
            return '';
        }
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
            $timeArray = ['created_at','updated_at' , 'login_time'];
            foreach ($search as $key => $value) {
                if(in_array($key,['name','english_name','title'])){
                    $model = $model->where($key,'like','%'.trim($value).'%');
                } else if (in_array($key,$timeArray)){
                    if(is_array($value)){
                        $model = $model->whereBetween($key,$value);
                    }
                } else if(is_array($value) && !in_array($key,$timeArray)){
                    $model = $model->whereIn($key,$value);
                } else {
                    $model = $model->where($key,$value);
                }
            }
        }
        return $model;
    }

}
