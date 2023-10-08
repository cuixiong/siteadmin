<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Http\Models\Position;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Rule;
use Modules\Admin\Http\Models\SelectTxt;
use Modules\Admin\Http\Models\User;

class CommonController extends Controller
{
    /**
     * 查询账号信息和账号权限
     */
    public function info(Request $request)
    {
        // 菜单权限
        $roles['menus'] = Rule::where('type',1)->select('vue_route')->get()->toArray();
        $roles['menus'] = array_keys(array_column($roles['menus'],null,'vue_route'));
        // 操作权限
        $roles['operation'] = Rule::where('type',2)->select('vue_route')->get()->toArray();
        $roles['operation'] = array_keys(array_column($roles['operation'],null,'vue_route'));

        // 角色名称
        $roles['role'] = Role::where('id',$request->user->role_id)->value('name');
        // 职位信息
        $position = Position::where('id',$request->user->position_id)->first();
        $data = $request->user;
        $data['position'] = $position['name'];
        $data['positionId'] = $position['id'];
        $data['roles'] = $roles;
        ReturnJson(true,'登陆成功',$data);
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

    /**
     * 临时用的列表表头
     */
    public function filters()
    {
        $data = [];
        $users = User::select(['id','name'])->get()->toArray();
        // 创建者
        $data['Creaters'] = $users;
        array_unshift($data['Creaters'],["id" =>'','name'=>'创建者']);
        // 修改者
        $data['Updaters'] = $users;
        array_unshift($data['Updaters'],["id" =>'','name'=>'更新者']);
        // 状态
        $data['States'] = SelectTxt::GetStatusTxt();
        // 权限类型
        $data['MuenTypes'] = SelectTxt::GetRuleTypeTxt();
        // 是否在职人员
        $data['IsOnJobList']  = SelectTxt::GetOnJobTxt();
        // 角色组
        $roles = Role::select(['id','name'])->get()->toArray();
        $data['Roles'] = $roles;
        array_unshift($data['Roles'],["id" =>'','name'=>'全部角色']);
        // 职位组
        $positions = Position::select(['id','name'])->get()->toArray();
        $data['Positions'] = $positions;
        array_unshift($data['Positions'],["id" =>'','name'=>'全部职位']);
        // 前端页面路由
        $VueRoute = Rule::where('type',1)->select(['id','name'])->get()->toArray();
        array_unshift($VueRoute,["id" =>'0','name'=>'顶级路由']);
        $data['VueRoute'] = $VueRoute;
        // 前端按钮路由
        $Operation = Rule::where('type',2)->select(['id','name'])->get()->toArray();
        array_unshift($Operation,["id" =>'','name'=>'按钮路由']);
        $data['Operation'] = $Operation;
        ReturnJson(TRUE,'请求成功',$data);
    }
}
