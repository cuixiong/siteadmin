<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class SiteUpdateLog extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['site_id', 'site_name', 'command', 'message', 'output', 'exec_status', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'];


    
    /**
     * 更新状态获取器
     */
    public function getExecStatusAttribute()
    {
        
        $text = '';
        if (isset($this->attributes['exec_status'])) {
            $logisticsTxtArray = array_column(SelectTxt::GetUpgradeTxt(), 'name', 'id');
            $text = $logisticsTxtArray[$this->attributes['exec_status']] ?? '';
        }
        return $text;
    }
}
