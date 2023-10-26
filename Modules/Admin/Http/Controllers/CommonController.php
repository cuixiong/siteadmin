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
        // $RuleModel = new Rule();
        // $menusQuery = $RuleModel->query();
        // $operationQuery = $RuleModel->query();
        // $role = Role::find($request->user->role_id);
        // if($role->is_super_administrator != 1){
        //     $menusQuery->whereIn('id',$role->rule_id);
        //     $operationQuery->whereIn('id',$role->rule_id);
        // }
        // // 菜单权限
        // $roles['menus'] = $menusQuery->where('type',1)->select('vue_route')->get()->toArray();
        // $roles['menus'] = array_keys(array_column($roles['menus'],null,'vue_route'));
        // // 操作权限
        // $roles['operation'] = $operationQuery->where('type',2)->select('vue_route')->get()->toArray();
        // $roles['operation'] = array_keys(array_column($roles['operation'],null,'vue_route'));
        // // 角色名称
        // $roles['role'] = $role->name;
        // // 职位信息
        // $position = Position::where('id',$request->user->position_id)->first();
        // $data = $request->user;
        // $data['position'] = $position['name'];
        // $data['positionId'] = $position['id'];
        // $data['roles'] = $roles;
        // ReturnJson(true,'登陆成功',$data);

        $data['userId'] = $request->user->id;
        $data['username'] = $request->user->email;
        $data['nickname'] = $request->user->nickname;
        $data['avatar'] = "https://oss.youlai.tech/youlai-boot/2023/05/16/811270ef31f548af9cffc026dfc3777b.gif";
        $res = (new Role)->GetRules(explode(',',$request->user->role_id));
        $data['roles'] = $res['code'];
        $rule_ids = $res['rule'];
        $RuleModel = new Rule();
        $perms = $RuleModel->where('type',2)->whereIn('id',$rule_ids)->select('perm')->get()->toArray();
        $data['perms'] = array_keys(array_column($perms,null,'perm'));
        ReturnJson(true,'请求成功',$data);
    }

    /**
     * 菜单栏
     * @param $request->user->rolo_id // 角色ID
     */
    public function menus(Request $request){
        // 角色ID
        $role_id = explode(',',$request->user->role_id);
        $data = (new Role)->GetRules($role_id);
        $rule_ids = $data['rule'];
        $roleCodes = $data['code'];
        // 查询type=1的菜单类型的权限信息
        $model = new Rule();
        $rules = $model->whereIn('id',$rule_ids)->where('type',1)->get()->toArray();
        // 递归分类权限
        $rules = $model->buildTree($rules,$roleCodes);
        // 返回菜单栏
        ReturnJson(TRUE,'',$rules);
    }
}
