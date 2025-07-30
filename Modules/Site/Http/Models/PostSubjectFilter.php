<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
// use Modules\Admin\Http\Models\User as Admin;

class PostSubjectFilter extends Base
{
    protected $table = 'post_subject_filter';

    // 设置允许入库字段,数组形式
    protected $fillable = ['keywords', 'user_id', 'status', 'sort', 'updated_by', 'created_by'];

    // protected $appends = ['username',];
    
    protected $attributes = [
        'status' => 1,
        'sort' => 100,
    ];

    const POST_SUBJECT_JOIN = 1;    // 加入过滤列表
    const POST_SUBJECT_READ = 2;    // 读取过滤列表

    
    // public function getUsernameAttribute()
    // {
    //     $res = Admin::where('id',$this->attributes['user_id'])->value('nickname');
    //     return $res;
    // }

}
