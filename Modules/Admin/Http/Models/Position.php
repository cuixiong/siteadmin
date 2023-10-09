<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Position extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','role_id','description','updated_by','created_by','site_id'];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['role_name'];

    /**
     * 默认角色名称
     */
    protected function getRoleNameAttribute()
    {
        if(isset($this->attributes['role_id']) && $this->attributes['role_id'] > 0)
        {
            $name = Role::where('id',$this->attributes['role_id'])->value('name');
        } else {
            $name = '';
        }
        return $name;
    }
}