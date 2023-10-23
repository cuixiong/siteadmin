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
            $list = (new Department)->GetList(['id','parent_id as parentId','name','status','sort','created_at as createTime','updated_at as updateTime'],true,'parentId');
            ReturnJson(TRUE,'请求成功',$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
