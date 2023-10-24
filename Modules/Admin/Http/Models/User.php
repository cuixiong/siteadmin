<?php

namespace Modules\Admin\Http\Models;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Http\Models\Base;
class User extends Base
{
    /** 隐藏不需要输出的字段 */
    protected $hidden = ["password"];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['position_txt','roleNames','is_on_job_txt','deptName','genderLabel','avatar'];
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['name','nickname','email','password','role_id','position_id','is_on_job','is_on_job','status','gender','mobile','department_id','created_by','updated_by'];


    /**
     * 密码修改器
     */
    protected function setPasswordAttribute($value)
    {
        // 不为空的情况下才修改密码
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
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
    public function getRoleNamesAttribute()
    {
        $roleName = '';
        if(isset($this->attributes['role_id'])){
            $roleName = Role::where('id',$this->attributes['role_id'])->value('name');
        }
        return $roleName;
    }

    /**
     * 在职状态文本获取器
     */
    public function getIsOnJobTxtAttribute()
    {
        $lists = SelectTxt::GetOnJobTxt();
        $text = '';
        if(isset($this->attributes['is_on_job']))
        {
            foreach ($lists as $list) {
                if($list['id'] == $this->attributes['is_on_job']){
                    $text = $list['name'];
                }
            }
        }
        return $text;
    }

    /**
     * 部门文本获取器
     */
    public function getDeptNameAttribute()
    {
        $text = '';
        if(isset($this->attributes['department_id']))
        {
            $text = Department::where('id',$this->attributes['department_id'])->value('name');
        }
        return $text;
    }

    /**
     * 性别获取器
     */
    public function getGenderLabelAttribute(){
        $text = '';
        if(isset($this->attributes['gender']))
        {   
            $lists = SelectTxt::GetGenderTxt();
            foreach ($lists as $list) {
                if($list['id'] == $this->attributes['gender']){
                    $text = $list['name'];
                }
            }
        }
        return $text;
    }

    /**
     * 头像获取器
     */
    public function getAvatarAttribute(){
        return 'https://oss.youlai.tech/youlai-boot/2023/05/16/811270ef31f548af9cffc026dfc3777b.gif';
    }
}