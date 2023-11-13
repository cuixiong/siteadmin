<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class EmailLog extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['status','send_email_id','emails','updated_by','created_by'];

    public static function AddLog($status,$sendEmailId,$emails,$data = [])
    {
        $model = new EmailLog();
        $model->status = $status;
        $model->send_email_id = $sendEmailId;
        $model->emails = is_array($emails) ? implode(',',$emails) : $emails;
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
}