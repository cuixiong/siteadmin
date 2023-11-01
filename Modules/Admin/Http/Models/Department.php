<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Department extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['parent_id','name','sort','status','created_by','updated_by','default_role'];

    /**
     * 角色ID修改器
     */
    protected function setDefaultRoleAttribute($value)
    {
        if(is_array($value))
        {
            $this->attributes['default_role'] = implode(',',$value);
        }
    }
    /**
     * 角色ID获取器
     */
    public function getDefaultRoleAttribute($value)
    {
        if(isset($this->attributes['default_role']))
        {
            $value = explode(',',$this->attributes['default_role']);
            foreach ($value as &$map) {
                $map = intval($map);
            }
            return $value;
        }
        return [];
    }
}