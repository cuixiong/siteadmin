<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Role extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['name','rule_id','status','description','updated_by','created_by','site_rule_id','code','data_scope','is_super_administrator','sort'];

    /**
     * 权限ID获取器
     */
    public function getRuleIdAttribute($value)
    {
        if(!empty($value)){
            $value = explode(",",$value);// 切割成数组
        }
        return $value;
    }

    public function IsSuperList()
    {
        return [
            ['id' => '0','name'=>'否'],
            ['id' => '1','name'=>'是'],
        ];
    }

    /**
     * 权限ID修改器
     */
    public function setRuleIdAttribute($value)
    {
        if(!empty($value)){
            $value = implode(",",$value);// 转换成字符串
        }
        $this->attributes['rule_id'] = $value;
    }
}