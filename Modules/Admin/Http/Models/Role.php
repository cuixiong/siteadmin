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

    /**
     * 获取角色所有权限ID
     * @param string/array $roles 角色ID
     * @param array $data
     */
    public function GetRules($ids,$key = 'all'){
        if(!is_array($ids)){
            $ids = explode(',',$ids);
        }
        // 查询角色信息
        $roles = self::whereIn('id',$ids)->get();
        $rule_ids = [];// 当前账号的权限id
        $role_code = [];// 当前账号的归属角色code
        foreach ($roles as $role) {
            $rule_ids = array_merge($rule_ids,$role->rule_id);
            $role_code[] = $role->code;
        }
        $rule_ids = empty($rule_ids) ? [] : array_unique($rule_ids);
        $role_code = empty($role_code) ? [] : array_unique($role_code);
        if($key == 'rule'){
            return $rule_ids;
        }
        if($key == 'code'){
            return $role_code;
        }
        $data = [
            'rule' => $rule_ids,
            'code' => $role_code,
        ];
        return $data;
    }
}