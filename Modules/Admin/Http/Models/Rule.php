<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
use App\Observers\OperationLog;
class Rule extends Base
{
    // 下面即是允许入库的字段，数组形式
    protected $fillable = ['parent_id','name','english_name','path','component','redirect','perm','route','icon','type','sort','category','visible','keepAlive','created_by','updated_by','category'];

    protected static function booted()
    {
        static::observe(OperationLog::class);
    }
    /**
     * 递归分类权限
     * @param $rules 权限数组
     */
    public function buildTree($rules,$roleCodes, $parentId = 0,)
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
                'roles' => $roleCodes,
            ];
            $data['name'] = ($item['parent_id'] > 0 && $item['type'] == 1) ? ucfirst($item['path']) : $item['path'];
            if(!empty($item['redirect'])){
                $data['redirect'] = $item['redirect'];
            }
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($rules,$roleCodes, $item['id']);
                if (!empty($children)) {
                    $data['children'] = $children;
                }
                $tree[] = $data;
            }
        }
        return $tree;
    }

    /**
     * components 修改器
     * @param $value
     * @return string
     */
    public function setTypeAttribute()
    {
        if($this->attributes['type'] == 'CATALOG'){
            $this->attributes['component'] = 'Layout';
        }
    }
}