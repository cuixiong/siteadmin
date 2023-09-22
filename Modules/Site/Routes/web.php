<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;// 这个是通过请求头/请求参数识别路由
InitializeTenancyByRequestData::$header = 'X-Site'; // 这个设置你的请求头名，如果需要只设置请求参数识别的话就设置成null
InitializeTenancyByRequestData::$queryParameter = null;// 这里设置请求参数KEY，如果只需要设置请求头识别租户

Route::prefix('site')->middleware([
    'web',
    InitializeTenancyByRequestData::class,// 识别租户中间件
])->group(function () {
    Route::get('site/index','SiteController@index');
    Route::get('site/user','SiteController@user');
});
