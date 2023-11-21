<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Dictionary;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Role;

class RoleController extends CrudController
{
    /**
     * 返回某个角色已拥有的Admin模块权限Ids
     */
    public function adminId(Request $request)
    {
        $id = $request->id;
        if(empty($id)){
            ReturnJson(false,'id is empty');
        }
        $rule_ids = Role::where('id',$id)->value('rule_id');
        if(!empty($rule_ids)){
            // 使用array_map()和intval()将数组中的值转换为整数
            $rule_ids = array_map('intval',$rule_ids);
        } else {
            $rule_ids = [];
        }
        ReturnJson(TRUE,trans('lang.request_success'),$rule_ids);
    }

    /**
     * 递归树状value-label格式
     */
    public function option (Request $request) {
        try {
            $list = (new Role)->GetList(['id','id as value','name as label'],false,'',['status'=>1]);
            ReturnJson(TRUE,trans('lang.request_success'),$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * Admin模块新增权限
     */
    public function adminRule(Request $request) {
        try {
            if(empty($request->roleId)){
                ReturnJson(FALSE,'roleId is empty');
            }
            if(empty($request->id)){
                ReturnJson(FALSE,'id is empty');
            }
            $input = $request->all();
            $input['rule_id'] = $input['roleId'];
            unset($input['roleId']);
            $input['updated_by'] = $request->user->id;
            $record = $this->ModelInstance()->findOrFail($request->id);
            if(!$record->update($input)){
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * Site模块新增权限
     */
    public function siteRule(Request $request) {
        try {
            if(empty($request->site_rule_id)){
                ReturnJson(FALSE,'site_rule_id is empty');
            }
            if(empty($request->id)){
                ReturnJson(FALSE,'id is empty');
            }
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            if(!$record->update($input)){
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 返回某个角色已拥有的Admin模块权限Ids
     */
    public function siteId(Request $request)
    {
        $id = $request->id;
        if(empty($id)){
            ReturnJson(false,'id is empty');
        }
        $rule_ids = Role::where('id',$id)->value('site_rule_id');
        if(!empty($rule_ids)){
            // 使用array_map()和intval()将数组中的值转换为整数
            $rule_ids = array_map('intval',$rule_ids);
        } else {
            $rule_ids = [];
        }
        ReturnJson(TRUE,trans('lang.request_success'),$rule_ids);
    }

    /**
     * get dict options
     * @return Array
     */
    public function options(Request $request)
    {
        $options = [];
        $codes = ['Switch_State','Administrator'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        ReturnJson(TRUE,'', $options);
    }
}
