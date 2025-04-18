<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class TeamMember extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name',
        'image',
        'position',
        'status',
        'sort',
        'updated_by',
        'created_by',
        'describe',
        'show_product',
        'region_name',
        'language',
        'img2',
        'phone',
        'icon', //区域小图标
        'national_flag', //国旗
        'area',
        'experience',
        'custom',
        'industry_id',
        'is_analyst',
        'attention_level'
        ];
    protected $appends = [
        'industry_name'
    ];
    // Image修改器
    public function setImageAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['image'] = $value;
        return $value;
    }
    // Image获取器
    public function getImageAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }


    public function setImg2Attribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['img2'] = $value;
        return $value;
    }
    public function getImg2Attribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }
    // Image获取器
    public function getIconAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }

    public function setIconAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['icon'] = $value;
        return $value;
    }


    public function getNationalFlagAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }

    public function setNationalFlagAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['national_flag'] = $value;
        return $value;
    }


    /**
     * 行业ID获取器
     */
    public function getIndustryIdAttribute($value)
    {
        if(isset($this->attributes['industry_id']))
        {
            $value = explode(',',$this->attributes['industry_id']);
            $value = ProductsCategory::whereIn('id',$value)->where('status',1)->pluck('id')->toArray();
            foreach ($value as &$map) {
                $map = intval($map);
            }
            return $value;
        }
        return null;
    }

    /**
     * 行业ID修改器
     */
    protected function setIndustryIdAttribute($value)
    {
        $this->attributes['industry_id'] = $value && is_array($value) ? implode(',',$value) : '';
    }

    // 行业名称获取器
    public function getIndustryNameAttribute($value){
        if(isset($this->attributes['industry_id'])){
            $ids = $this->attributes['industry_id'] ? explode(',',$this->attributes['industry_id']) : [];
            $value = ProductsCategory::whereIn('id',$ids)->pluck('name');
            $value = $value ? $value : [];
            return $value;
        }
        return [];
    }
}
