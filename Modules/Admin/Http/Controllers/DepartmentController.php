<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\Department;

class DepartmentController extends CrudController
{
    /**
     * 查询列表页
     * @param $request 请求信息
     * @param Array $where 查询条件数组 默认空数组
     */
    public function list (Request $request) {
        try {
            $where = [];
            if(!empty($request->keywords)){
                $where['name'] = ['like','%'.$request->keywords.'%'];
            }
            if(isset($request->status)){
                $where['status'] = $request->status;
            }
            $filed = ['id','parent_id','name','status','sort','created_at as createTime','updated_at as updateTime'];
            $list = (new Department)->GetList($filed,true,'parent_id',$where);
            ReturnJson(TRUE,'请求成功',$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
    /**
     * 递归树状value-label格式
     */
    public function option () {
        try {
            $list = (new Department)->GetList(['id','id as value','parent_id','name as label'],true,'parent_id');
            ReturnJson(TRUE,'请求成功',$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
