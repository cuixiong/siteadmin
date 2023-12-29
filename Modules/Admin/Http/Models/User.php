<?php

namespace Modules\Admin\Http\Models;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Http\Models\Base;
use Illuminate\Database\Eloquent\Casts\Attribute;
class User extends Base
{
    /** 隐藏不需要输出的字段 */
    protected $hidden = ["password"];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['deptName','genderLabel','avatar','deptId','roleIds','ruleText'];
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['name','nickname','email','password','role_id','status','gender','mobile','department_id','created_by','updated_by','sort'];


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
            $lists = DictionaryValue::where('code','gender')->where('status',1)->select(['value','name'])->get()->toArray();
            foreach ($lists as $list) {
                if($list['value'] == $this->attributes['gender']){
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
            $value = Role::whereIn('id',$value)->where('status',1)->pluck('id')->toArray();
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

    /**
     * 角色文本获取器
     */
    public function getRuleTextAttribute($value)
    {
        if(!empty($this->attributes['role_id'])){
            $value = Role::whereIn('id',explode(",",$this->attributes['role_id']))->pluck('name');
            return $value;
        }
        return [];
    }
    
    /**
     * 登陆时间获取器
     * @param \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function loginAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('Y-m-d H:i:s',$value),
        );
    }

    /**
     * 处理查询列表条件数组
     * @param $model moxel
     * @param $search 搜索条件
     */
    public function HandleSearch($model,$search){
        if(!is_array($search)){
            $search = json_decode($search,true);
        }
            $search = array_filter($search,function($v){
                if(!(empty($v) && $v != "0")){
                    return true;
                }
            });
            if(!empty($search)){
                if(isset($search['created_by'])){
                    unset($search['created_by']);
                }
                if(isset($search['updated_by'])){
                    unset($search['updated_by']);
                }
                $timeArray = ['created_at','updated_at'];
                foreach ($search as $key => $value) {
                    if(in_array($key,['name','english_name','title'])){
                        $model = $model->where($key,'like','%'.trim($value).'%');
                    } else if (in_array($key,$timeArray)){
                        if(is_array($value)){
                            $model = $model->whereBetween($key,$value);
                        }
                    } else if(is_array($value) && !in_array($key,$timeArray)){
                        $model = $model->whereIn($key,$value);
                    } else if($key == 'role_id'){
                        $model = $model->whereRaw('FIND_IN_SET('.$value.',role_id)');
                    } else {
                        $model = $model->where($key,$value);
                    }
                }
            }
        return $model;
    }
}