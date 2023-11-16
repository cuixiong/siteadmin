<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class EmailLog extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['status','send_email_id','emails','email_scenes','updated_by','created_by'];
    protected $appends = ['status_text'];

    public static function AddLog($status,$sendEmailId,$emails,$scenesId,$data = [])
    {
        // var_dump($scenesId);die;
        $model = new EmailLog();
        $model->status = $status;
        $model->send_email_id = $sendEmailId;
        $model->emails = is_array($emails) ? implode(',',$emails) : $emails;
        $model->email_scenes = $scenesId;
        $model->data = $data;
        $model->save();
    }

    public function setDataAttribute($value)
    {
        if(is_array($value) && !empty($value)){
            $this->attributes['data'] = json_encode($value);
        } else {
            $this->attributes['data'] = '';
        }
        return $value;
    }


    public function getStatusTextAttribute()
    {
        $value = DictionaryValue::GetNameAsCode('EmailLog_Status',$this->status);
        return $value;
    }

    public function getSendEmailAttribute()
    {
        if(isset($this->attributes['send_email_id'])){
            return Email::where('id',$this->attributes['send_email_id'])->value('email');
        }
    }

    public function getEmailScenesAttribute()
    {
        if(isset($this->attributes['email_scenes'])){
            return EmailScene::where('id',$this->attributes['email_scenes'])->value('name');
        }
    }
}