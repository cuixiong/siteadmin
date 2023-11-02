<?php

namespace Modules\Admin\Http\Models;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Http\Models\Base;
class User extends Base
{
    /** 隐藏不需要输出的字段 */
    protected $hidden = ["password"];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['deptName','genderLabel','avatar','deptId','roleIds'];
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['name','nickname','email','password','role_id','status','gender','mobile','department_id','created_by','updated_by'];


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
     * 部门文本获取器
     */
    public function getDeptNameAttribute($value)
    {
        if(isset($this->attributes['department_id']))
        {
            $text = Department::where('id',$this->attributes['department_id'])->value('name');
            return $text;
        }
        return null;
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

    /**
     * 部门ID获取器
     */
    public function getDeptIdAttribute($value)
    {
        if(isset($this->attributes['department_id']))
        {
            $value = $this->attributes['department_id'];
            return $value;
        }
        return null;
    }

    /**
     * 角色ID获取器
     */
    public function getRoleIdsAttribute($value)
    {
        if(isset($this->attributes['role_id']))
        {
            $value = explode(',',$this->attributes['role_id']);
            foreach ($value as &$map) {
                $map = intval($map);
            }
            return $value;
        }
        return null;
    }

    /**
     * 角色ID修改器
     */
    protected function setRoleIdAttribute($value)
    {
        $this->attributes['role_id'] = implode(',',$value);
    }

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model,$request){
        if(!empty($request->keywords)){
            $model = $model->where('nickname','like','%'.$request->keywords.'%')
                            ->orWhere('name',$request->keywords)
                            ->orWhere('id',$request->keywords)
                            ->orWhere('mobile',$request->keywords);
        }
        if(!empty($request->deptId)){
            $model = $model->where('department_id',$request->deptId);
        }
        if(isset($request->status)){
            $model = $model->where('status',$request->status);
        }
        if(!empty($request->startTime)){
            $startTime = strtotime($request->startTime);
            $model = $model->where('created_at','>=',$startTime);
        }
        if(!empty($request->endTime)){
            $endTime = strtotime($request->endTime);
            $model = $model->where('created_at','<=',$endTime);
        }
        if(!empty($request->search)){
            $model = $this->HandleSearch($model,$request->search);
        }
        return $model;
    }
}