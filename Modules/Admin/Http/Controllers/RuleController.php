<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Models\Rule;

class RuleController extends CrudController
{

    /**
     * 查询列表页
     * @param use Illuminate\Http\Request;
     */
    public function list(Request $request) {
        try {
            $list = (new Rule)->GetList('*',true,'parent_id');
            ReturnJson(TRUE,'请求成功',$list);
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
            $uri = $route->uri;
            if(!empty($name)){
                if(!empty($module)){
                    if(strpos($uri,$module.'/') !== false){
                        array_push($result,['name' => $name,'route' => $uri]);
                    }
                } else {
                    array_push($result,['name' => $name,'route' => $uri]);
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
        $routes = $this->RoutesList('admin');
        ReturnJson(TRUE,'请求成功',$routes);
    }

    /**
     * 返回权限value-label格式行数据
     */
    public function option()
    {
        $list = (new Rule)->GetList(['id','id as value','title as label','parent_id'],true,'parent_id');
        ReturnJson(TRUE,'请求成功',$list);
    }
}
