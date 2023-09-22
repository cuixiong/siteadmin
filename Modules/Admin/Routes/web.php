<?php
use App\Http\Middleware\JwtMiddleware;
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

Route::prefix('admin')->middleware([
    'api',
    JwtMiddleware::class
])->group(function() {
    Route::get('/', 'AdminController@index');
    Route::post('site/create', 'SiteController@create'); // 站点新增
    Route::get('site/git', 'SiteController@git'); // 站点GIT
});
