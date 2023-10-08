<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Role extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['name','rule_id','status','description','updated_by','created_by','site_rule_id'];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['is_super_txt'];

    /**
     * 权限ID获取器
     */

    public function getRuleIdAttribute($value)
    {
        if(!empty($value)){
            $ids = explode(",",$value);// 切割成数组
            $value = Rule::whereIn('id',$ids)->select(['id','name'])->get()->toArray();
        }
        return $value;
    }

    /**
     * 是否属于超级管理员文本获取器
     */
    public function getIsSuperTxtAttribute()
    {
        if(isset($this->attributes['is_super_administrator'])){
            $lists = $this->IsSuperList();
            foreach ($lists as $list) {
                if($this->attributes['is_super_administrator'] == $list['id']){
                    $name = $list['name'];
                    break;
                }
            }
        } else {
            $name = '';
        }
        return $name;
    }

    public function IsSuperList()
    {
        return [
            ['id' => '0','name'=>'否'],
            ['id' => '1','name'=>'是'],
        ];
    }

}