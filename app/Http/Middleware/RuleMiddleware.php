<?php
namespace App\Http\Middleware;
use Closure;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Rule;

class RuleMiddleware
{
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
            if(!$request->user->is_super){
                $route = $request->route();
                // 获取当前角色已分配ID
                $rule_ids = (new Role)->GetRules($request->user->role_id,'rule');
                $action = $route->getAction();

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
    }    
}
