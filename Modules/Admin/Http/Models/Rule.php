<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class Rule extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['parent_id','title','vue_route','controller','action','route','icon','type','status','sort','created_by','updated_by','category'];
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
        if(!empty($value)){
            $this->attributes['route'] = $value;
            // 切割路由获得控制器和方法名
            list($this->attributes['controller'],$this->attributes['action']) = explode('@',$value);
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
            // 临时添加KEY S
            $data = [];
            $data['path'] = $item['path'];
            $data['component'] = $item['component'];
            $data['meta'] = [
                'title' => $item['title'],
                'hidden' => false,
                'icon' => $item['icon'],
                'keepAlive' =>  true,
                'roloes' => ["ADMIN"]
            ];
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($rules, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                    $data['children'] = $children;
                } else {
                    $item['children'] = [];
                    $data['children'] = [];
                }
                // $tree[] = $item;
                $tree[] = $data;
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
            $name = $this->where('id',$this->attributes['parent_id'])->value('title');
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
            $lists = SelectTxt::GetStatusTxt();
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
            $lists = SelectTxt::GetRuleTypeTxt();
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
}