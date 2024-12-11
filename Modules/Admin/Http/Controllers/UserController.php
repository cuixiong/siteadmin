<?php

namespace Modules\Admin\Http\Controllers;

use App\Exports\UsersExport;
use App\Imports\UserImport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends CrudController
{
    /**
     * 单个新增
     * @param $request 请求信息
     */
    protected function store(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $input['created_by'] = $request->user->id;
            $input['department_id'] = $input['deptId'];
            $input['role_id'] = $input['roleIds'];
            unset($input['deptId'],$input['roleIds']);
            $record = $this->ModelInstance()->create($input);
            if(!$record){
                ReturnJson(FALSE,trans('lang.add_error'));
            }
            ReturnJson(TRUE,trans('lang.add_success'),['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 修改状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeStatus(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            if($request->status != 1){
                //关闭状态需要重新登录
                $model = User::where('id',$request->id)->first();
                $model->updated_at = time();
                $token = JWTAuth::fromUser($model);//生成token
                $passRes = $model->save();
                if($passRes > 0) {
                    $tokenKey = 'login_token_'.$request->id;
                    Redis::del($tokenKey);
                }
                $record->token = $token;
            }
            $record->status = $request->status;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


    /**
     * AJax单个更新
     * @param $request 请求信息
     */
    protected function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $input['updated_by'] = $request->user->id;
            $input['department_id'] = $input['deptId'];
            $input['role_id'] = $input['roleIds'];
            $record = $this->ModelInstance()->findOrFail($request->id);

            //邮箱校验
            if($record->email != $input['email'] && !empty($input['email'])){
                $cnt = $this->ModelInstance()->where("email" , $input['email'])->count();
                if($cnt > 0){
                    ReturnJson(false, trans('lang.eamail_unique'));
                }
                $model = User::where('id',$request->id)->first();
                $model->email = $input['email'];
                $token = JWTAuth::fromUser($model);//生成token
                $rs = $model->save();
                if($rs > 0) {
                    $tokenKey = 'login_token_'.$request->id;
                    Redis::del($tokenKey);
                }
                $input['token'] = $token;
            }

            if(!empty($request->password)){
                $model = User::where('id',$request->id)->first();
                $model->password = Hash::make($request->password);
                $token = JWTAuth::fromUser($model);//生成token
                $passRes = $model->save();
                if($passRes > 0) {
                    $tokenKey = 'login_token_'.$request->id;
                    Redis::del($tokenKey);
                }
                $input['token'] = $token;
            }


            if(!$record->update($input)){
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * update user info
     * @param $request
     */
    public function updateInfo(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->user->id);
            if(!empty($request->password)){
                $model = User::where('id',$request->user->id)->first();
                $model->password = Hash::make($request->password);
                $token = JWTAuth::fromUser($model);//生成token
                $passRes = $model->save();
                if($passRes > 0) {
                    $tokenKey = 'login_token_'.$request->user->id;
                    Redis::del($tokenKey);
                }
                $input['token'] = $token;
            }else{
                unset($input['password']);
            }
            if(!$record->update($input)){
                ReturnJson(FALSE,trans('lang.update_error'));
                exit;
            }
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * get is login info
     */
    public function UserInfo(Request $request){
        try {
            $data = $this->ModelInstance()->findOrFail($request->user->id);
            ReturnJson(TRUE,'',$data);
        } catch (\Exception $e) {
            ReturnJson(TRUE,$e->getMessage());
        }
    }

    /**
     * get dict options
     * @return Array
     */
    public function options(Request $request)
    {
        $options = [];
        $codes = ['Switch_State','Gender'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        $options['roles'] = (new Role)->GetList(['id as value','name as label']);
        ReturnJson(TRUE,'', $options);
    }

    public function import(Request $request)
    {
        try {
            Excel::import(new UserImport, request()->file('file'));
            ReturnJson(true,trans('lang.upload_successful'));
        } catch (\Exception $e) {
            ReturnJson(false,$e->getMessage());
        }

    }

    public function export()
    {
        try {
            return Excel::download(new UsersExport,'users.xlsx');
        } catch (\Exception $e) {
            ReturnJson(false,$e->getMessage());
        }
    }

    public function download()
    {
        $file = public_path('import_template/UserTemplate.xls');
        return response()->download($file);
    }
}
