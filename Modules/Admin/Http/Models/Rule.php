<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Rule extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['parent_id','name','vue_route','controller','action','route','icon','type','status','sort','create_by','update_by'];
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['parent_name','status_txt','menus_txt'];

    /**
     * 设置route字段拦截器和填充controller、action字段
     *
     * @param  string  $value
     * @return void
     */
    public function setRouteAttribute($value)
    {
        $this->attributes['route'] = $value;
        // 切割路由获得控制器和方法名
        list($this->attributes['controller'],$this->attributes['action']) = explode('@',$value);
    }

    /**
     * 递归分类权限
     * @param $rules 权限数组
     */
    public function buildTree($rules, $parentId = 0)
    {
        $tree = [];
        foreach ($rules as $item) {
            // var_dump($item['parent_id'],$parentId);
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($rules, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                } else {
                    $item['children'] = [];
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 父级名字获取器
     */
    public function getParentNameAttribute()
    {
        if(isset($this->attributes['parent_id']) && $this->attributes['parent_id'] > 0)
        {
            $name = $this->where('id',$this->attributes['parent_id'])->value('name');
        } else {
            $name = '';
        }
        return $name;
    }

    /**
     * 状态文本获取器
     */
    public function getStatusTxtAttribute()
    {
        if(isset($this->attributes['status']))
        {
            $lists = $this->StatusList();
            foreach ($lists as $list) {
                if($list['id'] == $this->attributes['status']){
                    $name = $list['state'];
                    break;
                }
            }
        } else {
            $name = '';
        }
        return $name;
    }

    /**
     * 菜单文本获取器
     */
    public function getMenusTxtAttribute()
    {
        if(isset($this->attributes['type']))
        {
            $lists = $this->MuenList();
            foreach ($lists as $list) {
                if($list['id'] == $this->attributes['type']){
                    $name = $list['name'];
                    break;
                }
            }
        } else {
            $name = '';
        }
        return $name;
    }

    /**
     * 权限状态列表
     */
    public function StatusList()
    {
        return [['id'=>'','state'=>'状态'],['id'=>'0','state'=>'禁用'],['id'=>1,'state'=>'正常']];
    }

    /**
     * 权限状态列表
     */
    public function MuenList()
    {
        return [['id'=>'','name'=>'权限类型'],['id'=>'1','name'=>'菜单'],['id'=>2,'name'=>'操作'],['id'=>3,'name'=>'外链']];
    }
}