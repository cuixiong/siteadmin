<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
class User extends Base
{
    /** 隐藏不需要输出的字段 */
    protected $hidden = ["password"];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['position_txt','role','is_on_job_txt'];
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['name','email','password','status','login_time','role_id','position_id','is_on_job','verify_status','created_by','updated_by'];


    /**
     * 密码修改器
     */
    protected function setPasswordAttribute($value)
    {
        // 不为空的情况下才修改密码
        if (!empty($value)) {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * 职位名称获取器
     */
    public function getPositionTxtAttribute()
    {
        if(isset($this->attributes['position_id'])){
            $positionName = Position::where('id',$this->attributes['position_id'])->value('name');
        } else {
            $positionName = '';
        }
        return $positionName;
    }
    /**
     * 角色名称获取器
     */
    public function getRoleAttribute()
    {
        $roleName = Role::where('id',$this->attributes['role_id'])->value('name');
        return $roleName;
    }

    /**
     * 在职状态文本获取器
     */
    public function getIsOnJobTxtAttribute()
    {
        $lists = $this->IsOnJobList();
        $text = '';
        foreach ($lists as $list) {
            if($list['id'] == $this->attributes['is_on_job']){
                $text = $list['name'];
            }
        }
        return $text;
    }
    /** 在职状态列表 */
    public function IsOnJobList(){
        $list = [
            ['id' => '','name' => '全部'],
            ['id' => '0','name' => '离职'],
            ['id' => '1','name' => '在职'],
        ];
        return $list;
    }

    /** 账号状态列表 */
    public function StatusList(){
        $list = [
            ['id' => '','name' => '全部'],
            ['id' => '0','name' => '禁用'],
            ['id' => '1','name' => '正常'],
        ];
        return $list;
    }
}