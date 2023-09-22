<?php

namespace Modules\Admin\Http\Models;

use Illuminate\Database\Eloquent\Model;
class Rule extends Model
{

    public $FieldRule = [
        'store' => [
            'name'          => 'required',
            'vue_route'     => 'required',
            'type'          => 'required',
            'status'        => 'required'
        ],
        'destroy' => [
            'id'   =>  'required'
        ]
    ];

    public $FieldMessage = [
        'store' => [
            'name.required'         =>  '权限名称不能为空',
            'vue_route.required'    =>  '前端路由不能为空',
            'type.required'         =>  '请选择类型',
            'status.required'       =>  '请选择状态',
        ],
        'destroy' => [
            'id.required'   =>  'ID不能为空'
        ]
    ];

    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['parent_id','name','vue_route','controller','action','route','icon','type','status','sort'];

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
}