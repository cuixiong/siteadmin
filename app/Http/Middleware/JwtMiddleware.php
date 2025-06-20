<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Modules\Admin\Http\Models\Role;
class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {  //获取到用户数据，并赋值给$user
                return response()->json([
                    'code' => 'B001',
                    'message' => '账号不存在'
                ]);
            }
            $token = $request->header('Authorization');
            $token = trim(str_replace('Bearer','',$token));

            $tokenKey = 'login_token_'.$user->id;
            $cacheToken = Redis::get($tokenKey);
            //判断缓存token是否一致
            if($cacheToken != $token) {
                if ($user->token != $token) {
                    return response()->json([
                                                'code' => -200,
                                                                  'message' => 'token is error'
                                            ]);
                }
            }

            // 国外站点token处理
            $is_center = env('is_center');
            if(!$is_center){
                //当前传入的token 与 用户的token不一致, 需要重新登陆
                if($token != $user->token){
                    //删除缓存
                    $tokenKey = 'login_token_'.$user->id;
                    Redis::del($tokenKey);
                    return response()->json([
                                                'code' => -200,
                                                'message' => 'token is error'
                                            ]);
                }
            }


            // 将用户信息存储在请求中，以便后续使用
            $is_super = Role::whereIn('id',explode(',',$user->role_id))->where('is_super',1)->count();
            $user->is_super = $is_super > 0 ? true : false;
            $request->user = $user;
            return $next($request);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'code' => -200,
                'message' => 'token 过期' , //token已过期
            ]);

        } catch (TokenInvalidException $e) {
            return response()->json([
                'code' => -200,
                'message' => 'token 无效',  //token无效
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'code' => -200,
                'message' => '缺少token' , //token为空
            ]);
        }
    }
}
