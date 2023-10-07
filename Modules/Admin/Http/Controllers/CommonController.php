<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Rule;

class CommonController extends Controller
{
    /**
     * 左侧菜单栏
     */
    public function info(Request $request)
    {
        $data = [
            'code' => 200,
            'message' => '登陆成功',
            'data' => [
                'created_at' => "2023-09-19",
                'email' => "1192063282@qq.com",
                'id' => 1209,
                'is_on_job' => 1,
                'name' => "chongdianbao",
                'position' => "管理组织",
                'positionId' => 20,
                'status' => 1,
                'updated_at' => "2023-09-19",
                'roles' => [
                    'role' => "普通用户",
                    'menus' => [
                        "Permission",
                        "AdminUserList",// 用户管理
                        "AdminRuleList",// 权限管理
                        "AdminRoleList",// 角色管理
                        "AdminRoleStore",// 新增角色
                        "RoleModifyPermission",// 编辑角色
                        "AdminPositionList",// 职位管理
                        "PublisherList",//出版商管理
                        "AddPublisher",
                        "AdminPublisherList",
                        'AdminSiteList',
                    ],
                    'operation' => [
                        "add_permission",
                        "edit_permission",
                        "del_permission",
                        "del_all_permission",
                        "add_role",
                        "edit_role",
                        "del_role ",
                        "del_all_role",
                        "add_user",
                        "edit_user",
                        "del_user",
                        "del_all_user",
                        "del_all_user",
                        //
                        "del_role"
                    ]
                ]
            ]
        ];
        return response()->json($data);
    }

    /**
     * 菜单栏
     * @param $request->user->rolo_id // 角色ID
     */
    public function menus(Request $request){
        // 角色ID
        $role_id = $request->user->role_id;
        // 查询角色信息
        $role = Role::where('id',$role_id)->first();
        // 当前角色的归属权限ID
        $rule_ids = $role->rule_id;
        $rule_ids = explode(",",$rule_ids);
        // 查询type=1的菜单类型的权限信息
        $model = new Rule();
        $rules = $model->whereIn('id',$rule_ids)->where('type',1)->get()->toArray();
        // 递归分类权限
        $rules = $model->buildTree($rules);
        // 返回菜单栏
        ReturnJson(TRUE,'',$rules);
    }
}
