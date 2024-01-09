<?php
namespace Modules\Site\Http\Controllers;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Dictionary;
use Modules\Admin\Http\Models\DictionaryValue;

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
    public function options(Request $request){
        $options = [];
        $codes = ['Switch_State','Navigation_Menu_Type','Is_Single_Page'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        $options['menus'] = $this->ModelInstance()->where('status',1)->select('id as value','name as label')->get()->toArray();
        ReturnJson(TRUE,'', $options);
    }
}