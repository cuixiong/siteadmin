<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Rule;

class CommonController extends Controller
{
    /**
     * 查询账号信息和账号权限
     */
    public function info(Request $request)
    {
        $data = [
            'userId' => $request->user->id,
            'username' => $request->user->name,
            'nickname' => $request->user->nickname,
            'avatar' => "https://oss.youlai.tech/youlai-boot/2023/05/16/811270ef31f548af9cffc026dfc3777b.gif",
        ];
        $is_super = (new Role)->whereIn('id',explode(',',$request->user->role_id))->where('is_super',1)->count();
        $res = (new Role)->GetRules(explode(',',$request->user->role_id));
        $data['roles'] = $res['code'];
        $rule_ids = $res['rule'];
        $RuleModel = new Rule();
        if($is_super > 0){
            $perms = $RuleModel->where('type','BUTTON')->where(['visible' => 1,'category' => 1])->pluck('perm');
        } else {
            $perms = $RuleModel->where('type','BUTTON')->whereIn('id',$rule_ids)->where(['visible' => 1,'category' => 1])->pluck('perm');
        }
        $data['perms'] = $perms;
        ReturnJson(true,trans('lang.request_success'),$data);
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
        $fields = ['id','parent_id','path','component','redirect','perm','icon','type','sort','category','visible','keepAlive','updated_by','updated_at','created_at','created_by'];
        if($request->HeaderLanguage == 'en'){
            $fields = array_merge($fields,['english_name as name']);
        } else {
            $fields = array_merge($fields,['name']);
        }
        $is_super = (new Role)->whereIn('id',explode(',',$request->user->role_id))->where('is_super',1)->count();
        if($is_super > 0){
            $rules = $model->select($fields)->whereIn('type',['CATALOG','MENU'])->where(['visible' => 1,'category' => 1])->get()->toArray();
        } else {
            $rules = $model->select($fields)->whereIn('id',$rule_ids)->whereIn('type',['CATALOG','MENU'])->where(['visible' => 1,'category' => 1])->get()->toArray();
        }
        // 递归分类权限
        $rules = $model->buildTree($rules,$roleCodes);
        // 返回菜单栏
        ReturnJson(TRUE,'',$rules);
    }
}
