<?php
/**
 * RequestLog.php UTF-8
 * 请求日志
 *
 * @date    : 2024/10/15 15:10 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Admin\Http\Models;


class BanWhiteList extends Base {
    protected $table = 'ban_white_list';
    // 设置允许入库字段,数组形式
    protected $fillable = ['id', 'type', 'ban_str', 'remark', 'sort', 'status'];


    /**
     * Register the model events for updating.
     * @return void
     */
    protected static function boot() {
        parent::boot();
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
                if(in_array($key,['name','ban_str','remark'])){
                    $model = $model->where($key,'like','%'.trim($value).'%');
                } else if (in_array($key,$timeArray)){
                    if(is_array($value)){
                        $model = $model->whereBetween($key,$value);
                    }
                } else if(is_array($value) && !in_array($key,$timeArray)){
                    $model = $model->whereIn($key,$value);
                }elseif($key == 'created_by' && !empty($value )){
                    $userIds = \Modules\Admin\Http\Models\User::where('nickname', 'like', '%' . $value . '%')->pluck('id')->toArray();
                    $model = $model->whereIn('created_by',$userIds);
                } else {
                    $model = $model->where($key,$value);
                }
            }
        }
        return $model;
    }


}
