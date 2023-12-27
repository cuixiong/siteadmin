<?php
namespace Modules\Site\Http\Controllers;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
class MenuController extends CrudController{
    /**
     * 查询列表页
     * @param use Illuminate\Http\Request;
     */
    public function list(Request $request) {
        try {
            $search = $request->input('search');
            $list = $this->ModelInstance()->GetList('*',false,'parent_id',$search);
            $list = array_column($list,null,'id');
            $childNode = array(); // 储存已递归的ID
            foreach ($list as &$map) {
                $children = $this->tree($list,'parent_id',$map['id'],$childNode);
                if($children){
                    $map['children'] = $children;
                }
            }
            foreach ($list as &$map) {
                if (in_array($map['id'], $childNode)) {
                    unset($list[$map['id']]);
                }
            }
            $list = array_values($list);
            ReturnJson(TRUE,trans('lang.request_success'),$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 递归获取树状列表数据
     * @param $list
     * @param $key 需要递归的键值，这个键值的值必须为整数型
     * @param $parentId 父级默认值
     * @return array $res
     */
    public function tree($list,$key,$parentId = 0,&$childNode) {

        $tree = [];
        foreach ($list as $item) {
            if ($item[$key] == $parentId) {
                $childNode[] = $item['id'];// 储存已递归的ID
                $children = $this->tree($list,$key,$item['id'],$childNode);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }

        }
        return $tree;
    }
}