<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class PostSubjectStrategyUser extends Base
{
    protected $table = 'post_subject_strategy_user';

    // 设置允许入库字段,数组形式
    protected $fillable = ['strategy_id', 'user_id', 'num', 'status', 'sort', 'updated_by', 'created_by'];
}
