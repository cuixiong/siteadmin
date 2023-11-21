<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Rule;

class RuleController extends CrudController
{

    /**
     * 查询列表页
     * @param use Illuminate\Http\Request;
     */
    public function list(Request $request) {
        try {
            $search = $request->input('search');
            $list = (new Rule)->GetList('*',true,'parent_id',$search);
            ReturnJson(TRUE,trans('lang.request_success'),$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 返回某一模块路由
     * @param string $module
     * @param array $result
     */
    private function RoutesList($module = '')
    {
        $routes = Route::getRoutes()->get();
        $result = [];
        foreach ($routes as $route) {
            $name = $route->getName();
            if(!empty($name)){
                $uri = $route->uri;
                $action = $route->getAction();
                $controller = $action['controller'];
                if(!empty($module)){
                    if(strpos($controller,$module) !== false){
                        array_push($result,['name' => $name,'route' => $uri,'value' => $controller]);
                    }
                } else {
                    array_push($result,['name' => $name,'route' => $uri,'value' => $controller]);
                }
            }
        }
        return $result;
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
        $list = (new Rule)->GetListLabel(['id','id as value','name as label','parent_id'],true,'parent_id',['category' => 1,'visible' => 1]);
        ReturnJson(TRUE,trans('lang.request_success'),$list);
    }

    /**
     * 返回Site模块的权限value-label格式行数据
     */
    public function optionSite(Request $request)
    {
        $list = (new Rule)->GetListLabel(['id','id as value','name as label','parent_id'],true,'parent_id',['category' => 2,'visible' => 1]);
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
}
