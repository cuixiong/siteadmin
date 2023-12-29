<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Role extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['name','rule_id','status','description','updated_by','created_by','site_rule_id','code','data_scope','is_super_administrator','sort','site_id','is_super'];
    /**
     * 权限ID获取器
     */
    public function getRuleIdAttribute($value)
    {
        if(!empty($value)){
            $value = explode(",",$value);// 切割成数组
            $value = array_map('intval',$value);
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
     * @param string $key all/rule/code
     * @param int $siteId
     * @param array $data
     */
    public function GetRules($ids,$key = 'all',$siteId = 0){
        if(!is_array($ids)){
            $ids = explode(',',$ids);
        }
        // 查询角色信息
        $roles = self::whereIn('id',$ids)->where('status',1)->get();
        $rule_ids = [];// 当前账号的权限id
        $role_code = [];// 当前账号的归属角色code
        foreach ($roles as $role) {
            if(!empty($role->rule_id) && $siteId == 0){
                $rule_ids = array_merge($rule_ids,$role->rule_id);
            }
            if(!empty($role->site_id)){
                if($siteId > 0 && in_array($siteId,$role->site_id)){
                    if(!empty($role->site_rule_id)){
                        $rule_ids = array_merge($rule_ids,$role->site_rule_id);
                    }
                }
            }
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

    /**
     * 站点ID修改器
     */
    public function setSiteIdAttribute($value)
    {
        if(!empty($value) && is_array($value)){
            $value = implode(",",$value);// 转换成字符串
        }
        $value = empty($value)? "" : $value;
        $this->attributes['site_id'] = $value;
    }

    /**
     * 站点ID获取器
     */
    public function getSiteIdAttribute($value)
    {
        if(!empty($value)){
            $value = explode(",",$value);// 切割成数组
            $value = Site::whereIn('id',$value)->where('status',1)->pluck('id')->toArray();
            $value = array_map('intval',$value);
        }
        return $value;
    }

    /**
     * 站点权限ID修改器
     */
    public function setSiteRuleIdAttribute($value)
    {
        if(!empty($value)){
            $value = implode(",",$value);// 转换成字符串
        }
        $this->attributes['site_rule_id'] = $value;
    }

    /**
     * 站点权限ID获取器
     */
    public function getSiteRuleIdAttribute($value)
    {
        if(!empty($value)){
            $value = explode(",",$value);// 切割成数组
            $value = array_map('intval',$value);
        }
        return $value;
    }

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model,$request){
        if(!empty($request->keywords)){
            $model = $model->where('name','like','%'.$request->keywords.'%')
                            ->orWhere('id',$request->keywords);
        }
        // 超级管理员
        if(isset($request->is_super)){
            $model = $model->where('is_super',$request->is_super);
        }
        if(!empty($request->search)){
            $model = $this->HandleSearch($model,$request->search);
        }
        return $model;
    }
}