<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class TimedTask extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'name', 
        'type', 
        'do_command',
        'status',
        'sort',
        'log_path',
        'site_id',
        'day',
        'hour',
        'minute',
        'week_day',
        'log_path',
        'time_type', 
        'updated_by', 
        'created_by',
        'category',
        'command',
        'parent_id',
        'old_command',
        'task_id',
        'body',
    ];

    /**
     * 站点ID获取器
     */
    public function getSiteIdAttribute($value)
    {
        if(isset($this->attributes['site_id']))
        {
            $value = explode(',',$this->attributes['site_id']);
            $value = Site::whereIn('id',$value)->pluck('id')->toArray();
            foreach ($value as &$map) {
                $map = intval($map);
            }
            return $value;
        }
        return null;
    }
}
