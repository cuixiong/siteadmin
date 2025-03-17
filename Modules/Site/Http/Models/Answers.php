<?php
/**
 * Answers.php UTF-8
 * 问答
 *
 * @date    : 2025/3/11 10:12 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Models;
use Modules\Admin\Http\Models\User as Admin;

class Answers extends Base {
    protected $table = 'answers';
    // 设置允许入库字段,数组形式
    protected $fillable
        = ['id', 'question_id', 'content', 'sort', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at',
           'user_id', 'answer_at'];

    protected $appends = ['user_name' , 'answer_at_str' , 'question_title'];

    public function getUserNameAttribute()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->value('username');
    }

    public function getAnswerAtStrAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['answer_at']);
    }

    public function getQuestionTitleAttribute()
    {
        return $this->belongsTo(Questions::class, 'question_id', 'id')->value('title');
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
                if(in_array($key,['name','english_name','title' , 'content'])){
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

}
