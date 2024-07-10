<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\DictionaryValue;

class ViewProductsLog extends Base {
    protected $table   = 'view_products_log';
    protected $fillable
                       = [
            'user_id', 'product_id', 'ip', 'ip_addr', 'product_name', 'keyword',
            'view_cnt', 'view_date_str', 'sort', 'status'
        ];
    protected $appends = ['username'];

    public function getUsernameAttribute() {
        $userName = '游客';
        if (!empty($this->attributes['user_id'])) {
            $userName = User::find($this->user_id)->value("username");
        }

        return $userName;
    }

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model,$request){
        if(!empty($request->search)){
            $model = $this->HandleSearch($model,$request->search);
        }
        return $model;
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
                if(in_array($key,['ip', 'ip_addr', 'product_name', 'keyword'])){
                    $model = $model->where($key,'like','%'.trim($value).'%');
                } else if (in_array($key,$timeArray)){
                    if(is_array($value)){
                        $model = $model->whereBetween($key,$value);
                    }
                } else if(is_array($value) && !in_array($key,$timeArray)){
                    $model = $model->whereIn($key,$value);
                } else {
                    if($key == 'username'){
                        if($value == '游客    '){
                            $model = $model->where("user_id" , 0);
                        }else{
                            $userIdList = User::query()->where("username" , 'like','%'.trim($value).'%')->pluck("id")->toArray();
                            $model = $model->whereIn("user_id" , $userIdList);
                        }
                    }else {
                        $model = $model->where($key, $value);
                    }
                }
            }
        }
        return $model;
    }

}
