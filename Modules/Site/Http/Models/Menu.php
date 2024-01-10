<?php

namespace Modules\Site\Http\Models;
use Modules\Admin\Http\Models\DictionaryValue;
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
        'sort',
        'banner_short_title',
        'prompt'
    ];
    protected $appends = ['parent_name','type_name'];

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
    public function getBannerPcAttribute($value)
    {
        $value = $value ? explode(",",$value) : [];
        return $value;
    }

    public function setBannerPcAttribute($value)
    {
        $value = is_array($value) ? implode(",",$value) : $value;
        $this->attributes['banner_pc'] = $value;
        return $value;
    }

    public function getBannerMobileAttribute($value)
    {
        $value = $value ? explode(",",$value) : [];
        return $value;
    }
    public function setBannerMobileAttribute($value)
    {
        $value = is_array($value) ? implode(",",$value) : $value;
        $this->attributes['banner_mobile'] = $value;
        return $value;
    }

    public function getTypeNameAttribute()
    {
        if(empty($this->attributes['type'])){
            return '';
        }
        $value = DictionaryValue::where('code','Navigation_Menu_Type')
            ->where('value',$this->attributes['type'])
            ->value('name');
        return empty($value)? '' : $value;
    }
}