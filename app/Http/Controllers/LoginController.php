<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Admin\Http\Models\Department;
use Modules\Admin\Http\Models\Site;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * user login
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $email = $request->get('username');
            $password = $request->get('password');
            $validator = Validator::make(['email' => $email,'password' => $password],[
                'email' => 'required|email:rfc,dns',
                'password' => 'required'
            ],[
                'email.required' => trans('lang.eamail_empty'),
                'email.email' => trans('lang.eamail_email'),
                'password.required' => trans('lang.password_empty'),
            ]);
            if ($validator->fails()) {
                ReturnJson(FALSE,$validator->errors()->first());
            }
            $model = User::where('email','=',$email)->first();
            if (!$model) {
                ReturnJson(false,trans('lang.eamail_undefined'));
            }
            if (!Hash::check($password, $model->password)) {
                ReturnJson(false,trans('lang.password_no_pass'));
            }
            if ($model->status == 0) {
                ReturnJson(false,trans('lang.accounts_disabled'));
            }
            if($request->header('Site')){
                $SiteCount = Site::where('name',$request->header('Site'))
                    ->where('status',1)->count();
                if($SiteCount == 0){
                    ReturnJson(false,trans('lang.site_undefined'));
                }
            }
            $token=JWTAuth::fromUser($model);//生成token
            if (!$token) {
                ReturnJson(false,'生成TOKEN失败');
            }
            $model->login_at = time();
            $model->save();
            ReturnJson(true,trans('lang.request_success'),[
                'accessToken' => $token,
                'expires' => auth('api')->factory()->getTTL() + 66240,
                'refreshToken' => null,
                'tokenType' =>  'Bearer'
            ]);
        } catch (\Exception $e){
            ReturnJson(false,$e->getMessage());
        }
    }

    /**
     * user register
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'email' => 'required|unique:users,email|email:rfc,dns',
                'department_id' => 'required',
                'password' => 'required',
            ],[
                'name.required' => trans('lang.name_empty'),
                'email.required' => trans('lang.eamail_empty'),
                'email.unique' => trans('lang.eamail_unique'),
                'email.email' => trans('lang.eamail_email'),
                'department_id.required' => trans('lang.department_empty'),
                'password.required' => trans('lang.password_empty'),
            ]);
            if ($validator->fails()) {
                ReturnJson(FALSE,$validator->errors()->first());
            }
            $input = $request->all();
            $model = new User();
            $model->email = $input['email'];
            $model->name = $input['email'];
            $model->nickname = $input['name'];
            $model->department_id = $input['department_id'];
            $model->password = Hash::make($request->get('password'));// 密码使用hash值
            // 注册时将部门选择默认的角色ID赋值到账号的role_id中
            $role_ids = Department::where('id',$model->department_id)->value('default_role');
            $model->role_id = implode(",",$role_ids);
            $model->status = 0;
            $model->gender = 0;
            $model->login_at = time();
            $model->created_by = 0;
            $model->created_at = time();
            if(User::where('email','=',$model->email)->first()){
                ReturnJson(false,trans('lang.eamail_unique'));
            }
            if($model->save())
            {
                (new SendEmailController)->register($model->id);
                $token=JWTAuth::fromUser($model);//生成token
                if(!$token){
                    ReturnJson(false,'注册成功,但是token生成失败');
                }
                ReturnJson(true,trans('lang.request_success'),['token' => $token,'status' => 1,'expires_in' => auth('api')->factory()->getTTL() + 66240,'id' => $model->id]);
            } else {
                ReturnJson(false,trans('lang.request_error'));
            }
        } catch (\Exception $e) {
            ReturnJson(false,$e->getMessage());
        }
    }
 
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();
        ReturnJson(true,trans('lang.request_success'));
    }
 
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth('api')->refresh();// 重新获取token
        return response()->json(['code' => 200,'message' => trans('lang.request_success'),'data' => ['token' => $token]]);
    }

    /**
     * Reset Password Request
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
                'token' =>'required',
            ], [
                'password.required' => trans('lang.password_empty'),
                'token.required' => trans('lang.code_empty'),
            ]);
            if ($validator->fails()) {
                ReturnJson(FALSE,$validator->errors()->first());
            }
            $token = base64_decode($request->token);
            list($email,$id) = explode('&',$token);
            $model = User::where('email',$email)->where('id',$id)->first();
            if (!$model) {
                ReturnJson(false,trans('lang.eamail_undefined'));
            }
            $model->password = Hash::make($request->get('password'));
            $model->save();
            ReturnJson(true,trans('lang.request_success'));
        } catch (\Exception $e) {
            ReturnJson(false,$e->getMessage());
        }
    }

    /**
     * activate accouunt email send
     * @param use Illuminate\Http\Request $request;
     * @return response Code
     */
    public function activate(Request $request){
        try {
            $token = $request->token;
            if(!isset($token) || empty($token)){
                ReturnJson(FALSE,trans('lang.token_empty'));
            }
            $token = base64_decode($request->token);
            list($email,$id) = explode('&',$token);
            $res = User::where('email',$email)->where('id',$id)->update(['status' => 1]);
            $res ? ReturnJson(TRUE,trans('lang.request_success')) : ReturnJson(FALSE,trans('lang.request_error'));
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}