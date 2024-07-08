<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Dictionary;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Site;

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
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        ReturnJson(TRUE,'', $options);
    }

    public function update(Request $request)
    {
        try {
            $count = $this->ModelInstance()->where('id','<>', $request->id)->where('code',$request->code)->count();
            if($count > 0){
                ReturnJson(FALSE,trans('lang.code_exists'));
            }
            $count = $this->ModelInstance()->where('id','<>', $request->id)->where('name',$request->name)->count();
            if($count > 0){
                ReturnJson(FALSE,trans('lang.role_name_exists'));
            }
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);

            if (!$record->update($input)) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    protected function list(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            // 总数量
            $total = $model->count();
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $record = $model->get()->toArray();

            $siteModel = new Site();
            foreach ($record as &$info){
                $info['siteList'] = [];
                if(!empty($info['site_id'] )){
                    $info['siteList'] = $siteModel->whereIn("id" , $info['site_id'])->select(["id" , 'name'])->get()->toArray();
                }
            }
            $data = [
                'total' => $total,
                'list'  => $record
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


}
