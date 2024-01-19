<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Page extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['page_id', 'content','status','sort', 'updated_by', 'created_by'];
    protected $appends = ['page_name'];

    // 页面名称获取器
    public function getPageNameAttribute()
    {
        if(isset($this->attributes['page_id'])){
            $value = Menu::where('id',$this->attributes['page_id'])->value('name');
            $value = $value ? $value : '';
            return $value;
        }
        return '';
    }
}