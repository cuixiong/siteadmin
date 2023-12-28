<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class EmailScene extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','title','body','email_sender_id','email_recipient','status','sort','action','updated_by','created_by','alternate_email_id'];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['email_sender_txt'];
    /**
     * 发件人名称获取器
     */
    protected function getEmailSenderTxtAttribute()
    {
        if(isset($this->attributes['email_sender_id']) && $this->attributes['email_sender_id'] > 0)
        {
            $name = Email::where('id',$this->attributes['email_sender_id'])->value('email');
        } else {
            $name = '';
        }
        return $name;
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
                $timeArray = ['created_at','updated_at'];
                foreach ($search as $key => $value) {
                    if(in_array($key,['name','email_recipient'])){
                        $model = $model->where($key,'like','%'.trim($value).'%');
                    } else if (in_array($key,$timeArray)){
                        if(is_array($value)){
                            $model = $model->whereBetween($key,$value);
                        }
                    } else if(is_array($value) && !in_array($key,$timeArray)){
                        $model = $model->whereIn($key,$value);
                    } else {
                        $model = $model->where($key,$value);
                    }
                }
            }
        return $model;
    }
}