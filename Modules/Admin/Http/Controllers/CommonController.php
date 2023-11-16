<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Rule;
use Modules\Admin\Http\Models\Site;

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
        $siteName = $request->header('Site');
        $siteId = Site::where('english_name',$siteName)->value('id');
        $siteId = $siteId ? $siteId : 0;
        $res = (new Role)->GetRules(explode(',',$request->user->role_id),'all',$siteId);
        $data['roles'] = $res['code'];
        $rule_ids = $res['rule'];
        $RuleModel = new Rule();
        $where = $siteId > 0 ? ['category' => 2,'status' => 1] : ['category' => 1,'status' => 1];
        if($is_super > 0){            
            $perms = $RuleModel->where('type','BUTTON')->where($where)->pluck('perm');
        } else {
            $perms = $RuleModel->where('type','BUTTON')->whereIn('id',$rule_ids)->where($where)->pluck('perm');
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
        $siteName = $request->header('Site');
        $siteId = Site::where('english_name',$siteName)->value('id');
        $siteId = $siteId ? $siteId : 0;
        $data = (new Role)->GetRules($role_id,'all',$siteId);
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
        $where = $siteId > 0 ? ['category' => 2,'status' => 1] : ['category' => 1,'status' => 1];
        if($is_super > 0){
            $rules = $model->select($fields)->whereIn('type',['CATALOG','MENU'])->where($where)->get()->toArray();
        } else {
            $rules = $model->select($fields)->whereIn('id',$rule_ids)->whereIn('type',['CATALOG','MENU'])->where($where)->get()->toArray();
        }
        // 递归分类权限
        $rules = $model->buildTree($rules,$roleCodes);
        // 返回菜单栏
        ReturnJson(TRUE,'',$rules);
    }

    /**
     * Switch Sites
     * @param use Illuminate\Http\Request;
     */
    public function switchSite(Request $request){
        $value = $request->header('Site');
        $key = config('other.NowSite').$request->user->id;
        Cache::put($key,$value);
        // 返回菜单栏
        ReturnJson(TRUE);
    }
}
