<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class EmailScene extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','title','body','email_sender_id','email_recipient','status','sort','action','updated_by','created_by'];
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
}