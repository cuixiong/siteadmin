<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class OperationLog extends Base
{
    protected $appends = ['category_text','type_text'];

    public function getCategoryTextAttribute($value)
    {
        if(isset($this->attributes['category'])){
            $text = DictionaryValue::GetNameAsCode('Route_Classification',$this->attributes['category']);
            return $text;
        }

    }

    public function getTypeTextAttribute($value)
    {
        if(isset($this->attributes['type'])){
            $text = DictionaryValue::GetNameAsCode('OperationLog_Type',$this->attributes['type']);
            return $text;
        }
    }
}