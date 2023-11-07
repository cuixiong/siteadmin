<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class PriceEditionValue extends Base
{
    // 设置可以入库的字段
    protected $fillable = ['name', 'edition_id', 'language_id', 'rules', 'notice', 'order', 'status', 'is_logistics', 'created_by', 'updated_by',];

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['language', 'logistics'];

    /**
     * 语言获取器
     */
    public function getLanguageAttribute()
    {
        $text = '';
        if (isset($this->attributes['language_id'])) {
            $text = Language::where('id', $this->attributes['language_id'])->value('name');
        }
        return $text;
    }

    /**
     * 物流获取器
     */
    public function getLogisticsAttribute()
    {
        $text = '';
        if (isset($this->attributes['is_logistics'])) {
            $logisticsTxtArray = array_column(SelectTxt::GetLogisticsTxt(), 'name', 'id');
            $text = $logisticsTxtArray[$this->attributes['is_logistics']] ?? '';
        }
        return $text;
    }
}
