<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;
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
// Login控制器
Route::post('login', [\App\Http\Controllers\LoginController::class,'login']);// 账号登陆
Route::post('register', [\App\Http\Controllers\LoginController::class,'register']);// 账号注册

// SendEmail控制器
Route::post('send-email/register', [\App\Http\Controllers\SendEmailController::class,'register']);// 注册发送邮箱接口
Route::middleware([
    'api',
    JwtMiddleware::class
])->group(function () {
    Route::get('logout', [\App\Http\Controllers\LoginController::class,'logout']);// 退出登陆
});