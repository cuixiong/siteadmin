<?php

use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

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
    JwtMiddleware::class, // JWT验证中间件
    'language' // 语言中间件
])->group(function() {
    // Common控制器
    Route::get('admin/common/info','CommonController@info')->name('INFO接口');
    Route::get('admin/common/menus','CommonController@menus')->name('菜单栏');
    Route::get('admin/common/switchSite',[Modules\Admin\Http\Controllers\CommonController::class,'switchSite'])->name('用户切换站点');
    Route::get('admin/rule/option','RuleController@option')->name('权限Admin模块option接口');
    Route::get('admin/rule/option-site','RuleController@optionSite')->name('权限Site模块option接口');
    Route::get('admin/role/adminId/{id}','RoleController@adminId')->name('Admin权限IDS');
    Route::get('admin/role/siteId/{id}','RoleController@siteId')->name('Site权限IDS');
    Route::get('admin/site/option','SiteController@option')->name('站点列表option');

    // User控制器
    Route::get('admin/user/form/{id}','UserController@form')->name('用户单查');
    Route::get('admin/user/list','UserController@list')->name('用户列表');
    Route::post('admin/update/info','UserController@updateInfo')->name('个人信息修改');
    Route::get('admin/user/info','UserController@UserInfo')->name('个人信息');
    Route::get('admin/user/options','UserController@options')->name('User字典数据');
    Route::post('admin/user/change-status','UserController@changeStatus')->name('用户修改状态');

    // Rule控制器
    Route::get('admin/rule/list','RuleController@list')->name('权限列表');
    Route::post('admin/rule/admin-routes','RuleController@GetAdminRoute')->name('Admin模块Route');
    Route::get('admin/rule/form/{id}','RuleController@form')->name('权限单查');
    Route::get('admin/rule/options',[Modules\Admin\Http\Controllers\RuleController::class,'options'])->name('权限字典数据');
    Route::post('admin/rule/change-status',[Modules\Admin\Http\Controllers\RuleController::class,'changeStatus'])->name('权限修改状态');


    // Role控制器
    Route::get('admin/role/list','RoleController@list')->name('角色列表');
    Route::get('admin/role/form/{id}','RoleController@form')->name('角色单查');
    Route::get('admin/role/option','RoleController@option')->name('角色option');
    Route::post('admin/role/adminRule','RoleController@adminRule')->name('Admin分配权限');
    Route::post('admin/role/siteRule','RoleController@siteRule')->name('Site分配权限');
    Route::get('admin/role/options',[Modules\Admin\Http\Controllers\RoleController::class,'options'])->name('角色字典数据');
    Route::post('admin/role/change-status',[Modules\Admin\Http\Controllers\RoleController::class,'changeStatus'])->name('权限修改状态');


    // Email控制器
    Route::get('admin/email/list','EmailController@list')->name('邮箱列表');
    Route::post('admin/email/changeStatus','EmailController@changeStatus')->name('邮箱状态改变');
    Route::get('admin/email/option','EmailController@option')->name('邮箱option列表');


    // EmailScene控制器
    Route::get('admin/email-scene/list','EmailSceneController@list')->name('发邮列表');
    Route::post('admin/email-scene/changeStatus','EmailSceneController@changeStatus')->name('发邮状态改变');

    // Dictionary控制器
    Route::get('admin/dictionary/list','DictionaryController@list')->name('字典列表');
    Route::get('admin/dictionary/form/{id}','DictionaryController@form')->name('字典单查');
    Route::get('admin/dictionary/options',[Modules\Admin\Http\Controllers\DictionaryController::class,'options'])->name('字典数据');
    Route::post('admin/dictionary/change-status',[Modules\Admin\Http\Controllers\DictionaryController::class,'changeStatus'])->name('字典修改状态');

    // DictionaryValue控制器
    Route::get('admin/dictionary-value/list/','DictionaryValueController@list')->name('字典项列表');
    Route::get('admin/dictionary-value/form/{id}','DictionaryValueController@form')->name('字典项单查');
    Route::get('admin/dictionary-value/get/{code}','DictionaryValueController@get')->name('字典项查询');
    Route::post('admin/dictionary-value/change-status',[Modules\Admin\Http\Controllers\DictionaryValueController::class,'changeStatus'])->name('字典项修改状态');

    // Server控制器
    Route::get('admin/server/list','ServerController@list')->name('服务器列表');
    Route::post('admin/server/changeStatus','EmailController@changeStatus')->name('服务器状态修改');

    // System控制器
    Route::get('admin/system/list','SystemController@list')->name('平台字段父级列表');
    Route::get('admin/system/form/{id}','SystemController@form')->name('平台字段父级单查');
    Route::get('admin/system-value/form/{id}','SystemController@formValue')->name('平台字段子级单查');
    Route::get('admin/system-value/list',[Modules\Admin\Http\Controllers\SystemController::class,'systemValueList'])->name('平台字段子级列表');
    Route::post('admin/system/change-status',[Modules\Admin\Http\Controllers\SystemController::class,'changeStatus'])->name('平台字段父级修改状态');
    Route::post('admin/system-value/change-status',[Modules\Admin\Http\Controllers\SystemController::class,'valueChangeStatus'])->name('平台字段子级修改状态');
    Route::get('admin/system/option',[Modules\Admin\Http\Controllers\SystemController::class,'option'])->name('平台字段option');
    Route::get('admin/system/value-list/{parent_id}',[Modules\Admin\Http\Controllers\SystemController::class,'valueList'])->name('平台字段全部子级列表');


    // Department控制器
    Route::get('admin/department/list','DepartmentController@list')->name('部门列表');
    Route::get('admin/department/form/{id}','DepartmentController@form')->name('部门单查');
    Route::get('admin/department/options',[Modules\Admin\Http\Controllers\DepartmentController::class,'options'])->name('部门字典数据');
    Route::post('admin/department/change-status',[Modules\Admin\Http\Controllers\DepartmentController::class,'changeStatus'])->name('部门修改状态');


    require __DIR__ . '/api_temp/loginNoRule.php';
    // Database控制器
    Route::post('admin/database/changeStatus','EmailController@changeStatus')->name('数据库状态修改');

    // Site控制器
    Route::get('admin/site/user-option',[Modules\Admin\Http\Controllers\SiteController::class,'UserOption'])->name('用户的站点下拉数据');

    // EmailLog控制器
    Route::get('admin/email-log/list',[Modules\Admin\Http\Controllers\EmailLogController::class,'list'])->name('邮箱日志列表');
    Route::get('admin/email-log/option',[Modules\Admin\Http\Controllers\EmailLogController::class,'option'])->name('邮箱日志option');

    // OperationLogController 控制器
    Route::get('admin/operation-log/list',[Modules\Admin\Http\Controllers\OperationLogController::class,'list'])->name('操作日志:数据列表');
    Route::get('admin/operation-log/destroy',[Modules\Admin\Http\Controllers\OperationLogController::class,'destroy'])->name('操作日志:删除操作');
});

/** 需要登陆并且需要验证权限的路由 */
Route::middleware([
    'api',
    JwtMiddleware::class, // JWT验证中间件
    'language', // 语言中间件
    // 'rule' // 权限验证中间件
])->group(function() {
    // User控制器
    Route::post('admin/user/store','UserController@store')->name('用户新增');
    Route::post('admin/user/destroy','UserController@destroy')->name('用户删除');
    Route::post('admin/user/update','UserController@update')->name('用户编辑');

    // Rule控制器
    Route::post('admin/rule/store','RuleController@store')->name('权限新增');
    Route::post('admin/rule/destroy','RuleController@destroy')->name('权限删除');
    Route::post('admin/rule/update','RuleController@update')->name('权限编辑');

    // Role控制器
    Route::post('admin/role/store','RoleController@store')->name('角色新增');
    Route::post('admin/role/update','RoleController@update')->name('角色编辑');
    Route::post('admin/role/destroy','RoleController@destroy')->name('角色删除');


    // Email控制器
    Route::post('admin/email/store','EmailController@store')->name('邮箱新增');
    Route::post('admin/email/destroy','EmailController@destroy')->name('邮箱删除');
    Route::post('admin/email/update','EmailController@update')->name('邮箱编辑');

    // EmailScene控制器
    Route::post('admin/email-scene/store','EmailSceneController@store')->name('发邮新增');
    Route::post('admin/email-scene/update','EmailSceneController@update')->name('发邮编辑');
    Route::post('admin/email-scene/destroy','EmailSceneController@destroy')->name('字典删除');


    // Dictionary控制器
    Route::post('admin/dictionary/store','DictionaryController@store')->name('字典新增');
    Route::post('admin/dictionary/update','DictionaryController@update')->name('字典编辑');
    Route::post('admin/dictionary/destroy','DictionaryController@destroy')->name('字典删除');

    // DictionaryValue控制器
    Route::post('admin/dictionary-value/store','DictionaryValueController@store')->name('字典项新增');
    Route::post('admin/dictionary-value/update','DictionaryValueController@update')->name('字典项编辑');
    Route::post('admin/dictionary-value/destroy','DictionaryValueController@destroy')->name('字典项删除');

    // Database控制器
    Route::get('admin/database/list','DatabaseController@list')->name('数据库列表');
    Route::post('admin/database/store','DatabaseController@store')->name('数据库新增');
    Route::post('admin/database/update','DatabaseController@update')->name('数据库编辑');
    Route::post('admin/database/destroy','DatabaseController@destroy')->name('数据库删除');

    // Server控制器
    Route::post('admin/server/store','ServerController@store')->name('服务器新增');
    Route::post('admin/server/update','ServerController@update')->name('服务器编辑');
    Route::post('admin/server/destroy','ServerController@destroy')->name('服务器删除');

    // System控制器
    Route::post('admin/system/store','SystemController@store')->name('设置Tab新增');
    Route::post('admin/system/update','SystemController@update')->name('设置Tab编辑');
    Route::post('admin/system/destroy','SystemController@destroy')->name('设置Tab删除');
    Route::post('admin/system-value/store',[Modules\Admin\Http\Controllers\SystemController::class,'systemValueStore'])->name('设置键值新增');
    Route::post('admin/system-value/update',[Modules\Admin\Http\Controllers\SystemController::class,'systemValueUpdate'])->name('设置键值编辑');
    Route::post('admin/system-value/destroy',[Modules\Admin\Http\Controllers\SystemController::class,'systemValueDestroy'])->name('设置键值删除');

    // Department控制器
    Route::post('admin/department/store','DepartmentController@store')->name('部门新增');
    Route::post('admin/department/update','DepartmentController@update')->name('部门编辑');
    Route::post('admin/department/destroy','DepartmentController@destroy')->name('部门删除');

    // EmailLog控制器
    Route::post('admin/email-log/destroy',[Modules\Admin\Http\Controllers\EmailLogController::class,'destroy'])->name('邮箱日志删除');
    
    require __DIR__ . '/api_temp/loginAndRule.php';
    
});

/** 不需要登陆也不需要验证权限的路由 */
Route::middleware([
    'api',
    'language' // 语言中间件
])->group(function() {
    Route::get('admin/department/option','DepartmentController@option')->name('部门option');
    // Position控制器
    Route::get('admin/position/list','PositionController@list')->name('职位列表');
    Route::get('admin/country/get-country','CountryController@getCountry')->name('国家列表');
    Route::get('admin/common/get-status','CommonController@getStatus')->name('获取状态 未知');
    Route::get('admin/common/filters','CommonController@filters')->name('公共数据');// 公共的列表表头和下拉数据

    Route::get('admin/test/test','TestController@TestPush')->name('测试接口');
    Route::get('admin/test/test01','TestController@Test01')->name('测试接口');
    Route::get('admin/test/test02','TestController@Test02')->name('测试接口');

    Route::get('baba',[\Modules\Admin\Http\Controllers\CronTask\DepartmentController::class,'test'])->name('测试接口');

    require __DIR__ . '/api_temp/other.php';
});

// 暂时测试路由
Route::get('/phpmyadmin', function () {
    return view('phpmyadmin');
});
