<?php
/**
 * AutoPostConfig.php UTF-8
 * 自动发帖配置
 *
 * @date    : 2025/3/21 14:21 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Models;
use Modules\Admin\Http\Models\User as Admin;

class AutoPostConfig extends Base {
    protected $table = 'auto_post_config';
    // 设置允许入库字段,数组形式
    protected $fillable
        = ['id', 'name', 'code', 'type', 'news_category_id', 'title_template_ids', 'content_template_ids', 'product_category_ids',
           'start_product_id', 'post_num', 'db_host', 'db_name', 'db_username', 'db_password', 'db_charset', 'domain',
           'created_by', 'created_at', 'updated_by', 'updated_at', 'sort', 'status'];



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
                if(in_array($key,['title_template_ids','content_template_ids','product_category_ids'])){
                    $model = $model->where($key,'like','%'.trim($value).'%');
                } else if (in_array($key,$timeArray)){
                    if(is_array($value)){
                        $model = $model->whereBetween($key,$value);
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


    const POST_SITE_TYPE_INSIDE = 1;
    const POST_SITE_TYPE_OUTSIDE = 2;

    public static function getSiteTypeList(){
        return [
            self::POST_SITE_TYPE_INSIDE => '站内',
            self::POST_SITE_TYPE_OUTSIDE => '站外',
        ];
    }

}
