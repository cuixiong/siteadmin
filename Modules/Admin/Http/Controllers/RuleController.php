<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RuleController extends CrudController
{
    /**
     * 左侧菜单栏
     */
    public function index(Request $request)
    {
        $data = [
            [
                'children' => [
                    [
                        'created_at' => "2022-06-27 15:13:33",
                        'created_by' => "王玉坛",
                        'id' => "2",
                        'name' => "权限管理",
                        'operation_vue' => "",
                        'order' => "1",
                        'p_id' => "1",
                        'pid' => "权限模块",
                        'route_vue' => "RightsManagePermission",
                        'status' => "1",
                        'statusName' => "有效",
                        'statusType' => true,
                        'type' => "菜单",
                        'type_id' => "1",
                        'updated_at' => "2022-08-31 09:09:05",
                        'updated_by' => "石玮豪"
                    ]
                ],
                'created_at' => "2022-06-27 15:11:55",
                'created_by' => "王玉坛",
                'id' => "1",
                'name' => "权限模块",
                'operation_vue' => "",
                'order' => "1",
                'p_id' => "0",
                'pid' => null,
                'route_vue' => "Permission",
                'status' => "1",
                'statusName' => "有效",
                'statusType' => true,
                'type' => "菜单",
                'type_id' => "1",
                'updated_at' => "2022-08-31 09:08:59",
                'updated_by' => "石玮豪"
            ]
        ];
        return response()->json(['code' => 200,'message' => '登陆成功','data' => $data ]);
    }
}
