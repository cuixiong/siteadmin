<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Models\Rule;

class RuleController extends CrudController
{
    /**
     * 左侧菜单栏
     */
    public function index(Request $request)
    {
        $this->list($request);
    }
    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $pageSize 页数
     * @param Array $where 查询条件数组 默认空数组
     */
    public function list ($request) {
        try {
            // $this->ValidateInstance($request);
            $model = $this->ModelInstance()->query();
            if(!empty($request->search)){
                $request->search = json_decode($request->search,TRUE);
                // 过滤条件数组，将空值的KEY过滤掉
                $search = array_filter($request->search,function($map){
                    if($map != ''){
                        return true;
                    }
                });
                // 使用Eloquent ORM来进行数据库查询
                foreach ($search as $field => $value) {
                    // 如果值是数组，则使用whereIn方法
                    if (is_array($value)) {
                        $model->whereIn($field, $value);
                    } else {
                        $model->where($field, $value);
                    }
                }
            }
            // 总数量
            $count = $model->count();
            // 总页数
            $pageCount = $request->pageSize > 0 ? ceil($count/$request->pageSize) : 1;
            // 当前页码数
            $page = $request->page ? $request->page : 1;
            $pageSize = $request->pageSize ? $request->pageSize : 100;

            // 查询偏移量
            if(!empty($request->page) && !empty($request->pageSize)){
                $model->offset(($request->page - 1) * $request->pageSize);
            }
            // 查询条数
            if(!empty($request->pageSize)){
                $model->limit($request->pageSize);
            }
            // 数据排序
            $order = $request->order ? $request->order : 'id';
            // 升序/降序
            $sort = (strtoupper($request->sort) == 'ASC') ? 'ASC' : 'DESC';

            $record = $model->orderBy($order,$sort)->get()->toArray();
            $record = (new Rule())->buildTree($record);
            $data = [
                'count' => $count,
                'pageCount' => $pageCount,
                'page' => $page,
                'pageSize' => $pageSize,
                'data' => $record
            ];
            ReturnJson(TRUE,'请求成功',$record);
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
