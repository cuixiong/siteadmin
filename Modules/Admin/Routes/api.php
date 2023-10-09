<?php

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
/** 需要登陆验证但不需要权限验证的路由 */
Route::middleware([
    'api',
    JwtMiddleware::class
])->group(function() {
    // Common控制器
    Route::post('admin-common/info','CommonController@info');// 账号信息
    Route::post('admin-common/menus','CommonController@menus');// 菜单栏
});

/** 需要登陆并且需要验证权限的路由 */
Route::middleware([
    'api',
    JwtMiddleware::class // JWT验证中间件
])->group(function() {
    // User控制器
    Route::post('admin/user/store','UserController@store');// 新增
    Route::post('admin/user/destroy','UserController@destroy');// 删除
    Route::post('admin/user/update','UserController@update');// 编辑
    Route::get('admin/user/list','UserController@list');// 列表
    Route::get('admin/user/filters','UserController@filters');// 表头数据

    // Rule控制器
    Route::post('admin/rule/store','RuleController@store');// 新增
    Route::get('admin/rule/list','RuleController@list');
    Route::get('admin/rule/index','RuleController@index');// 列表
    Route::get('admin/rule/filters','RuleController@filters');// 表头数据
    Route::post('admin/rule/destroy','RuleController@destroy');// 删除
    Route::post('admin/rule/update','RuleController@update');// 编辑

    // Role控制器
    Route::get('admin/role/list','RoleController@list');// 列表
    Route::post('admin/role/store','RoleController@store');// 新增
    Route::post('admin/role/update','RoleController@update');// 编辑
    Route::get('admin/role/filters','RoleController@filters');// 表头数据
    Route::post('admin/role/destroy','RoleController@destroy');// 删除

    // Position控制器
    Route::post('admin/position/store','PositionController@store');// 新增
    Route::post('admin/position/update','PositionController@update');// 编辑
    Route::get('admin/position/filters','PositionController@filters');// 表头数据
    Route::post('admin/position/destroy','PositionController@destroy');// 删除

    // Site控制器
    Route::post('admin/site/store','SiteController@store');// 新增
    Route::get('admin/site/list','SiteController@list');// 列表
    Route::post('admin/site/update','SiteController@update');// 更新
    Route::post('admin/site/destroy','SiteController@destroy');// 删除

    // Publisher控制器
    Route::get('admin/publisher/list','PublisherController@list');// 列表
    Route::get('admin/publisher/store','PublisherController@store');// 新增
    Route::post('admin/publisher/change-enable','PublisherController@changeEnable');// 修改状态
    Route::post('admin/publisher/destroy','PublisherController@destroy');// 修改状态
    Route::post('admin/publisher/update','PublisherController@update');// 修改数据

    // SiteRule控制器
    Route::post('admin/site-rule/store','SiteRuleController@store');// 新增
    Route::get('admin/site-rule/list','SiteRuleController@list');// 列表
    Route::post('admin/site-rule/destroy','SiteRuleController@destroy');// 删除
    Route::post('admin/site-rule/update','SiteRuleController@update');// 编辑
});

/** 不需要登陆也不需要验证权限的路由 */
// Position控制器
Route::get('admin/position/list','PositionController@list');
Route::get('admin/area/get-area','AreaController@getArea');// 列表
Route::get('admin/country/get-country','CountryController@getCountry');// 国家列表
Route::get('admin/publisher/get-publisher','PublisherController@getPublisher');// 出版商列表
Route::get('admin/language/get-language','LanguageController@getLanguage');// 语言
Route::get('admin/common/get-status','CommonController@getStatus');// 状态
Route::get('admin/common/filters','CommonController@filters');// 公共的列表表头和下拉数据
