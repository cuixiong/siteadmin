<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\App;
class LanguageMiddleware
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
            // 当前语言
            $langauge = $request->header('Langaue'); //不是浏览器的规范 (英文都写错了)

            //如果前端没传, 兼容浏览器的规范
            if (empty($langauge)) {
                $acceptLanguage = $request->header('Accept-Language');
                if (!empty($acceptLanguage)) {
                    if (strpos($acceptLanguage, 'zh') !== false) {
                        $langauge = 'zh';
                    } else {
                        $langauge = 'en';
                    }
                }
            }

            // 语言全局变量
            $request->HeaderLanguage = $langauge ? $langauge : 'en';
            // 设置当前语言
            App::setlocale($request->HeaderLanguage);
            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'B001',
                'message' => $e->getMessage() ,
            ]);

        }
    }
}
