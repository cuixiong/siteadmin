<?php

namespace Modules\Site\Http\Models;
class TemplateUse extends Base {
    protected $table = 'template_use';
    // 设置允许入库字段,数组形式
    protected $fillable
                     = [
            'user_id',          // 模版分类id
            'temp_id',          // 模版id
            'created_by',       // 创建者
            'updated_by',       // 编辑者
        ];

}
