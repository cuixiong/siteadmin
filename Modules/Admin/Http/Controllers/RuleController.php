<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Rule;

class RuleController extends CrudController
{
    public $childNode = array(); // 储存已递归的ID
    /**
     * 查询列表页
     * @param use Illuminate\Http\Request;
     */
    public function list(Request $request) {
        try {
            $search = $request->input('search');
            $list = (new Rule)->GetList('*',false,'parent_id',$search);
            $list = array_column($list,null,'id');
            foreach ($list as &$map) {
                $children = $this->tree($list,'parent_id',$map['id']);
                $map['children'] = $children ? $children : [];
            }
            foreach ($list as &$map) {
                if (in_array($map['id'], $this->childNode)) {
                    unset($list[$map['id']]);
                }
            }
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
    public function tree($list,$key,$parentId = 0) {

        $tree = [];
        foreach ($list as $item) {
            if ($item[$key] == $parentId) {
                $this->childNode[] = $item['id'];// 储存已递归的ID
                $children = $this->tree($list,$key,$item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }

        }
        return $tree;
    }

    /**
     * 返回某一模块路由
     * @param string $module
     * @param array $result
     */
    private function RoutesList($module = '')
    {
        $routes = Route::getRoutes();
        $result = [];
        foreach ($routes as $route) {
            $prefix = $route->getPrefix();
            $group = explode('/',$prefix);
            $group = isset($group[2]) ? $group[2] : null;
            $name = $route->getName();
            if(!empty($name)){
                $uri = $route->uri;
                $action = $route->getAction();
                $controller = $action['controller'];
                if(!empty($module)){
                    if(strpos($controller,$module) !== false){
                        if($group){
                            array_push($result,['name' => $name,'route' => $uri,'value' => $controller,'group' => $group]);
                        }
                    }
                }
            }
        }
        $groups = array_column($result,'group');
        $groups = array_unique($groups);
        $routeList = [];
        foreach ($groups as $key => $group) {
            $group_data = array_filter($result, function ($route) use ($group) {
                return $route['group'] == $group;
            });
            sort($group_data, SORT_REGULAR);
            $data = [];
            $name = '';
            for ($i = 0; $i < count($group_data); $i++) { 
                if(empty($name)){
                    if(isset($group_data[$i]['name'])){
                        $name = $group_data[$i]['name'];
                    }
                }
            }
            if(!empty($name)){
                $name = explode(':',$name);
                if(isset($name[0]) && !empty($name[0])){
                    $name = $name[0];
                }
            }
            $data['name'] = $name;
            $data['children'] = $group_data;
            $data['value'] = $group;
            $routeList[] = $data;
        }
        return $routeList;
    }
    /**
     * 返回Admin模块路由
     * @param use Illuminate\Http\Request $request
     */
    public function GetAdminRoute(Request $request)
    {
        $category = $request->category;
        if($category == "1"){
            $module = 'Admin';
        } else if($category == "2") {
            $module = 'Site';
        } else {
            $module = '';
        }
        $routes = $this->RoutesList($module);
        ReturnJson(TRUE,trans('lang.request_success'),$routes);
    }

    /**
     * 返回Admin模块权限value-label格式行数据
     */
    public function option(Request $request)
    {
        $list = (new Rule)->GetListLabel(['id','id as value','name as label','parent_id'],true,'parent_id',['category' => 1,'status' => 1]);
        ReturnJson(TRUE,trans('lang.request_success'),$list);
    }

    /**
     * 返回Site模块的权限value-label格式行数据
     */
    public function optionSite(Request $request)
    {
        $list = (new Rule)->GetListLabel(['id','id as value','name as label','parent_id'],true,'parent_id',['category' => 2,'status' => 1]);
        ReturnJson(TRUE,trans('lang.request_success'),$list);
    }

    /**
     * get dict options
     * @return Array
     */
    public function options(Request $request)
    {
        $options = [];
        $codes = ['Switch_State','Route_Classification','Menu_Type'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        ReturnJson(TRUE,'', $options);
    }

    /**
     * get dict options
     * @return Array
     */
    public function optionAddRule(Request $request)
    {
        $options = [];
        $codes = ['Menu_Type','Route_Classification','Switch_State','V_Show'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                if($map['code'] == 'V_Show' || $map['code'] == 'Switch_State' || $map['code'] == 'Route_Classification'){
                    $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
                } else {
                    $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
                }
            }
        }
        ReturnJson(TRUE,'', $options);
    }

    public function changeStatus(Request $request)
    {
        try {
            if(empty($request->id)){
                ReturnJson(FALSE,'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->status = $request->status;
            if(!$record->save()){
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            $childerIds = Rule::TreeGetChildIds($request->id);
            $this->ModelInstance()->whereIn('id',$childerIds)->update(['status' => $request->status]);
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) { 
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            $childerIds = Rule::TreeGetChildIds($request->id);
            $this->ModelInstance()->whereIn('id',$childerIds)->update(['status' => $request->status]);
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
