<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
class SystemValue extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['parent_id','name','key','value','type','status','switch','english_name','updated_by','created_by'];
    protected $appends = ['type_text'];

    public function getTypeTextAttribute()
    {
        if(!empty($this->attributes['type'])){
            $text = DictionaryValue::GetNameAsCode('Platform_Type',$this->attributes['type']);
            return $text;
        } else {
            return '';
        }
    }
}
