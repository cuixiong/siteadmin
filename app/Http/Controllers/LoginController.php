<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
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
            $UserModel = User::where('email','=',$email)->first();
            if (!$UserModel) {
                ReturnJson(false,'账号不存在');
            }
            if (!Hash::check($password, $UserModel->password)) {
                ReturnJson(false,'账号密码不正确');
            }
            if (!$UserModel->status == 1) {
                ReturnJson(false,'账号处于禁用状态，不允许登陆');
            }
            $token=JWTAuth::fromUser($UserModel);//生成token
            if (!$token) {
                ReturnJson(false,'生成TOKEN失败');
            }
            // 记录登陆时间
            $UserModel->login_time = date('Y-m-d H:i:s',);
            $UserModel->save();
            ReturnJson(true,'登陆成功',['token' => $token,'status' => 1]);
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
            $UserModel = new User();
            $UserModel->email = $request->get('email');
            $UserModel->name = $request->get('name');
            $UserModel->password = Hash::make($request->get('password'));// 密码使用hash值
            if(User::where('email','=',$UserModel->email)->first()){
                ReturnJson(false,'邮箱已存在请更换其他邮箱');
            }
            if($UserModel->save())
            {
                $token=JWTAuth::fromUser($UserModel);//生成token
                if(!$token){
                    ReturnJson(false,'注册成功,但是token生成失败');
                }
                ReturnJson(true,'注册成功',['token' => $token,'status' => 1]);
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
 
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 600
        ]);
    }
}