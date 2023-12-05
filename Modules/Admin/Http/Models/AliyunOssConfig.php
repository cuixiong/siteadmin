<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class AliyunOssConfig extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','domain','access_key_id','access_key_secret','endpoint','bucket','site_id','status','sort','updated_by','created_by'];
    protected $appends = ['site_name'];

    public function getSiteNameAttribute()
    {
        $text = '';
        if (isset($this->attributes['site_id'])) {
            $text = Site::where('id', $this->attributes['site_id'])->value('name');
        }
        return $text;
    }
}
