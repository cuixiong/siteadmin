<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Rule extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['parent_id','name','path','component','redirect','perm','module','controller','action','route','icon','type','status','sort','category','','created_by','updated_by','category'];

    /**
     * 设置route字段拦截器和填充module、controller、action字段
     *
     * @param  string  $value
     * @return void
     */
    public function setRouteAttribute($value)
    {
        if(!empty($value)){
            $this->attributes['route'] = $value;
            // 切割路由获得控制器和方法名
            list($this->attributes['module'],$this->attributes['controller'],$this->attributes['action']) = explode('/',$value);
        }
    }

    /**
     * 递归分类权限
     * @param $rules 权限数组
     */
    public function buildTree($rules, $parentId = 0)
    {
        $tree = [];
        foreach ($rules as $item) {
            $data = [];
            $data['path'] = $item['path'];
            $data['component'] = $item['component'];
            $data['meta'] = [
                'title' => $item['name'],
                'hidden' => $item['visible'] == 1 ? false : true,
                'icon' => $item['icon'],
                'keepAlive' => $item['keepAlive'] == 1 ? true : false,
                'roles' => ["ADMIN"]
            ];
            $data['name'] = ($item['parent_id'] > 0 && $item['type'] == 1) ? ucfirst($item['path']) : $item['path'];
            if(!empty($item['redirect'])){
                $data['redirect'] = $item['redirect'];
            }
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($rules, $item['id']);
                if (!empty($children)) {
                    $data['children'] = $children;
                }
                $tree[] = $data;
            }
        }
        return $tree;
    }
}