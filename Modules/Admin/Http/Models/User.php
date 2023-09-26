<?php

namespace Modules\Admin\Http\Models;

use Illuminate\Database\Eloquent\Model;
class User extends Model
{
    /** 隐藏不需要输出的字段 */
    protected $hidden = ["password"];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['position','role','is_on_job_txt'];
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['name','email','password','status','login_time','role_id','position_id','is_on_job','verify_status'];

    /**
     * 职位名称获取器
     */
    public function getPositionAttribute()
    {
        $positionName = Position::where('id',$this->attributes['position_id'])->value('name');
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