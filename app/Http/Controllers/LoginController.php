<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Position;
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
            $email = $request->get('name');
            $password = $request->get('password');
            $validator = Validator::make(['email' => $email,'password' => $password],[
                'email' => 'required|email:rfc,dns',
                'password' => 'required'
            ],[
                'email.required' => '邮箱不能为空',
                'email.email' => '邮箱格式不正确，请填写正确的邮箱',
                'password.required' => '密码不能为空'
            ]);
            if ($validator->fails()) {
                ReturnJson(FALSE,$validator->errors()->first());
            }
            $UserModel = User::where('email','=',$email)->first();
            if (!$UserModel) {
                ReturnJson(false,'账号不存在');
            }
            if (!Hash::check($password, $UserModel->password)) {
                ReturnJson(false,'账号密码不正确');
            }
            if ($UserModel->status == 0) {
                ReturnJson(false,'账号处于禁用状态，不允许登陆');
            }
            if ($UserModel->is_on_job == 0) {
                ReturnJson(false,'账号处于离职状态，不允许登陆');
            }
            $token=JWTAuth::fromUser($UserModel);//生成token
            if (!$token) {
                ReturnJson(false,'生成TOKEN失败');
            }
            // 记录登陆时间
            $UserModel->login_time = date('Y-m-d H:i:s',);
            $UserModel->save();
            ReturnJson(true,'登陆成功',[
                'accessToken' => $token,
                'expires' => auth('api')->factory()->getTTL() * 600,
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
                'position_id' => 'required',
                'password' => 'required',
            ],[
                'name.required' => '名字不能为空',
                'email.required' => '邮箱不能为空',
                'email.unique' => '邮箱已存在，请更换其他邮箱',
                'email.email' => '邮箱格式不正确，请填写正确的邮箱',
                'position_id.required' => '职位ID不能为空',
                'password.required' => '密码不能为空'
            ]);
            if ($validator->fails()) {
                ReturnJson(FALSE,$validator->errors()->first());
            }
            $UserModel = new User();
            $UserModel->email = $request->get('email');
            $UserModel->name = $request->get('name');
            $UserModel->position_id = $request->get('position_id');
            $UserModel->password = Hash::make($request->get('password'));// 密码使用hash值
            // 注册时将职位选择默认的角色ID赋值到账号的role_id中
            $UserModel->role_id = Position::where('id',$request->get('position_id'))->value('role_id');
            if(User::where('email','=',$UserModel->email)->first()){
                ReturnJson(false,'邮箱已存在请更换其他邮箱');
            }
            if($UserModel->save())
            {
                $token=JWTAuth::fromUser($UserModel);//生成token
                if(!$token){
                    ReturnJson(false,'注册成功,但是token生成失败');
                }
                ReturnJson(true,'注册成功',['token' => $token,'status' => 1,'expires_in' => auth('api')->factory()->getTTL() * 600,'id' => $UserModel->id]);
            } else {
                ReturnJson(false,'注册失败');
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
        return response()->json(['message' => 'Successfully logged out']);
    }
 
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth('api')->refresh();// 重新获取token
        return response()->json(['code' => 200,'message' => '刷新成功','data' => ['token' => $token]]);
    }
}