<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
InitializeTenancyByRequestData::$header = 'Site';
InitializeTenancyByRequestData::$queryParameter = null;


Route::get('check-user-email', [\App\Http\Controllers\LoginController::class,'checkUserEmail'])->middleware('language');// 用户邮箱校验
// Login控制器
Route::post('login', [\App\Http\Controllers\LoginController::class,'login'])->middleware('language');// 账号登陆
Route::middleware('language')->post('register', [\App\Http\Controllers\LoginController::class,'register']);// 账号注册
// SendEmail控制器
Route::get('send-email/register', [\App\Http\Controllers\SendEmailController::class,'register']);// 注册账号邮箱接口
Route::post('reset-password', [\App\Http\Controllers\LoginController::class,'resetPassword']);// 重置密码
Route::get('send-email/password', [\App\Http\Controllers\SendEmailController::class,'password']);// 重置密码邮箱接口
Route::get('activate', [\App\Http\Controllers\LoginController::class,'activate']);// 注册激活接口


Route::get('test1', [\App\Http\Controllers\TestCase::class,'test1']);//

/** 需要登陆权限路由 */
Route::middleware([
    'api',
    JwtMiddleware::class
])->group(function () {
    Route::get('logout', [\App\Http\Controllers\LoginController::class,'logout']);// 退出登陆
    Route::post('admin/send-email/test', [\App\Http\Controllers\SendEmailController::class,'test']);// 邮箱测试
    Route::get('admin/send-email/code', [\App\Http\Controllers\SendEmailController::class,'EmailCode']);// 邮箱代码
    Route::post('site/send-email/test', [\App\Http\Controllers\SiteEmailController::class,'test']);// 邮箱测试
    Route::get('site/send-email/code', [\App\Http\Controllers\SiteEmailController::class,'EmailCode']);// 邮箱代码
});

/** 需要登陆权限和租户路由 */
Route::middleware([
    'api',
    JwtMiddleware::class,
    InitializeTenancyByRequestData::class,
])->group(function () {
    Route::post('site/send-email/test', [\App\Http\Controllers\SiteEmailController::class,'test']);// 邮箱测试
    Route::get('site/send-email/code', [\App\Http\Controllers\SiteEmailController::class,'EmailCode']);// 邮箱代码
});
