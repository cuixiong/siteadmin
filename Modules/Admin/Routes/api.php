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
    Route::post('admin-common/info','CommonController@info')->name('账号信息');
    Route::post('admin-common/menus','CommonController@menus')->name('菜单栏');
});

/** 需要登陆并且需要验证权限的路由 */
Route::middleware([
    'api',
    JwtMiddleware::class // JWT验证中间件
])->group(function() {
    // User控制器
    Route::post('admin/user/store','UserController@store')->name('用户新增');
    Route::post('admin/user/destroy','UserController@destroy')->name('用户删除');
    Route::post('admin/user/update','UserController@update')->name('用户编辑');
    Route::get('admin/user/list','UserController@list')->name('用户列表'); 
    Route::get('admin/user/filters','UserController@filters')->name('用户表头');

    // Rule控制器
    Route::post('admin/rule/store','RuleController@store')->name('权限新增');
    Route::get('admin/rule/list','RuleController@list')->name('权限列表');
    Route::get('admin/rule/index','RuleController@index')->name('权限列表');
    Route::get('admin/rule/filters','RuleController@filters')->name('权限表头');
    Route::post('admin/rule/destroy','RuleController@destroy')->name('权限删除');
    Route::post('admin/rule/update','RuleController@update')->name('权限编辑');
    Route::get('admin/rule/admin-routes','RuleController@GetAdminRoute')->name('Admin模块Route');

    // Role控制器
    Route::get('admin/role/list','RoleController@list')->name('角色列表');
    Route::post('admin/role/store','RoleController@store')->name('角色新增');
    Route::post('admin/role/update','RoleController@update')->name('角色编辑');
    Route::get('admin/role/filters','RoleController@filters')->name('角色表头');
    Route::post('admin/role/destroy','RoleController@destroy')->name('角色删除');

    // Position控制器
    Route::post('admin/position/store','PositionController@store')->name('职位新增');
    Route::post('admin/position/update','PositionController@update')->name('职位编辑');
    Route::get('admin/position/filters','PositionController@filters')->name('职位表头');
    Route::post('admin/position/destroy','PositionController@destroy')->name('职位删除');

    // Site控制器
    Route::post('admin/site/store','SiteController@store')->name('站点新增');
    Route::post('admin/site/list','SiteController@list')->name('站点列表');
    Route::post('admin/site/update','SiteController@update')->name('站点更新');
    Route::post('admin/site/destroy','SiteController@destroy')->name('站点删除');
    Route::post('admin/site/move-up-site','SiteController@moveUpSite')->name('站点升级');
    Route::post('admin/site/message','SiteController@message')->name('站点测试');
    // Publisher控制器
    Route::get('admin/publisher/list','PublisherController@list')->name('出版商列表');
    Route::get('admin/publisher/store','PublisherController@store')->name('出版商新增');
    Route::post('admin/publisher/destroy','PublisherController@destroy')->name('出版商删除');
    Route::post('admin/publisher/update','PublisherController@update')->name('出版商修改');

    // Language控制器
    Route::get('admin/language/list','LanguageController@list')->name('语言列表');
    Route::post('admin/language/store','LanguageController@store')->name('语言新增');
    Route::post('admin/language/destroy','LanguageController@destroy')->name('语言删除');
    Route::post('admin/language/update','LanguageController@update')->name('语言编辑');

    // Region控制器
    Route::get('admin/region/list','RegionController@list')->name('地区列表');
    Route::post('admin/region/store','RegionController@store')->name('地区新增');
    Route::post('admin/region/destroy','RegionController@destroy')->name('地区删除');
    Route::post('admin/region/update','RegionController@update')->name('地区编辑');

    // Email控制器
    Route::get('admin/email/list','EmailController@list')->name('邮箱列表');
    Route::post('admin/email/store','EmailController@store')->name('邮箱新增');
    Route::post('admin/email/destroy','EmailController@destroy')->name('邮箱删除');
    Route::post('admin/email/update','EmailController@update')->name('邮箱编辑');

    // EmailScene控制器
    Route::get('admin/email-scene/list','EmailSceneController@list')->name('发邮列表');
    Route::post('admin/email-scene/store','EmailSceneController@store')->name('发邮新增');
    Route::post('admin/email-scene/update','EmailSceneController@update')->name('发邮编辑');

    // Dictionary控制器
    Route::get('admin/dictionary/list','DictionaryController@list')->name('字典列表');
    Route::post('admin/dictionary/store','DictionaryController@store')->name('字典新增');
    Route::post('admin/dictionary/update','DictionaryController@update')->name('字典编辑');
    Route::post('admin/dictionary/destroy','DictionaryController@destroy')->name('字典删除');
    
    // DictionaryValue控制器
    Route::get('admin/dictionary-value/list','DictionaryValueController@list')->name('字典项列表');
    Route::post('admin/dictionary-value/store','DictionaryValueController@store')->name('字典项新增');
    Route::post('admin/dictionary-value/update','DictionaryValueController@update')->name('字典项编辑');
    Route::post('admin/dictionary-value/destroy','DictionaryValueController@destroy')->name('字典项删除');

    // Database控制器
    Route::get('admin/database/list','DatabaseController@list')->name('数据库列表');
    Route::post('admin/database/store','DatabaseController@store')->name('数据库新增');
    Route::post('admin/database/update','DatabaseController@update')->name('数据库编辑');
    Route::post('admin/database/destroy','DatabaseController@destroy')->name('数据库删除');

    // Server控制器
    Route::get('admin/server/list','ServerController@list')->name('服务器列表');
    Route::post('admin/server/store','ServerController@store')->name('服务器新增');
    Route::post('admin/server/update','ServerController@update')->name('服务器编辑');
    Route::post('admin/server/destroy','ServerController@destroy')->name('服务器删除');
});

/** 不需要登陆也不需要验证权限的路由 */
// Position控制器
Route::get('admin/position/list','PositionController@list')->name('职位列表');
Route::get('admin/country/get-country','CountryController@getCountry')->name('国家列表');
Route::get('admin/publisher/get-publisher','PublisherController@getPublisher')->name('出版商列表');
Route::get('admin/common/get-status','CommonController@getStatus')->name('获取状态 未知');
Route::get('admin/common/filters','CommonController@filters')->name('公共数据');// 公共的列表表头和下拉数据
