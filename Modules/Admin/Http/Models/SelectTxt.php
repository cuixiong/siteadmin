<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class SelectTxt extends Base
{
    /**
     * 状态文本
     */
    public static function GetStatusTxt()
    {
        return [
            ['id'=>'','state'=>'状态'],
            ['id'=>'0','state'=>'禁用'],
            ['id'=>1,'state'=>'正常']
        ];
    }

    /**
     * 在职状态文本
     */
    public static function GetOnJobTxt()
    {
        return [
            ['id' => '','name' => '全部职位'],
            ['id' => '0','name' => '离职'],
            ['id' => '1','name' => '在职'],
        ];
    }

    /**
     * 权限类型文本
     */
    public static function GetRuleTypeTxt()
    {
        return [
            ['id'=>'','name'=>'权限类型'],
            ['id'=>'1','name'=>'菜单'],
            ['id'=>2,'name'=>'操作'],
            ['id'=>3,'name'=>'外链']
        ];
    }
}