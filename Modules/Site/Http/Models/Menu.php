<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class Menu extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name',
        'parent_id',
        'is_single',
        'type',
        'banner_pc',
        'banner_mobile',
        'banner_title',
        'banner_short_title',
        'banner_content',
        'link',
        'seo_title',
        'seo_keyword',
        'seo_description',
        'show_home',
        'title',
        'status',
        'updated_by',
        'created_by',
        'sort'
    ];
    protected $appends = ['parent_name'];

    public function getParentNameAttribute()
    {
        if(empty($this->attributes['parent_id'])){
            return '';
        }
        $value = self::where('id',$this->attributes['parent_id'])
            ->value('name');
        $value = empty($value)? '' : $value;
        return $value;
    }
}