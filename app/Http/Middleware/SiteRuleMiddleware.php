<?php
/**
 * SiteRuleMiddleware.php UTF-8
 * 站点端权限中间件
 *
 * @date    : 2024/6/13 11:43 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Http\Middleware;
use Closure;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Rule;
use Modules\Admin\Http\Models\Site;

class SiteRuleMiddleware
{
    public static $header = 'Site';
    public $ignoreList = [
        //'Modules\Admin\Http\Controllers\CommonController@info',
    ];

    public $ignoreControllerList = [
        //'Modules\Admin\Http\Controllers\CommonController',
    ];
    /**
     * Handle an incoming request.
     * 请求权限验证中间件
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
            if(!$request->user->is_super){
                //获取当前路由信息
                $route = $request->route();
                $action = $route->getAction();

                //如果是忽略权限校验接口直接放行
                if(in_array($action['controller'],$this->ignoreList)){
                    return $next($request);
                }

                //如果有忽略权限的控制器直接放行
                foreach ($this->ignoreControllerList as $ignoreController){
                    if(strpos($action['controller'],$ignoreController) !== false){
                        return $next($request);
                    }
                }

                $siteName = $request->header(static::$header);
                $siteId = (new Site())->where('name',$siteName)->value("id");

                // 获取当前角色已分配ID
                $rule_ids = (new Role)->GetRules($request->user->role_id,'rule', $siteId);
                // 获取当前用户所有角色的权限列表
                $rules = Rule::whereIn('id',$rule_ids)->pluck('route')->toArray();
                $rules = array_filter($rules);
                foreach ($rules as $value) {
                    $value = explode(',', $value);
                    if(in_array($action['controller'],$value)){
                        return $next($request);
                        break;
                    }
                }
                return response()->json([
                    'code' => 'B001',
                    'message' => 'No permission to operate',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'B001',
                'message' => $e->getMessage(),
            ]);

        }
        return $next($request);
    }
}
