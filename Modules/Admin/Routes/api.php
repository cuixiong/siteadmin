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
])->prefix('admin')->group(function () {
    Route::post('admin/update/info', 'UserController@updateInfo')->name('个人信息修改');
    // Common控制器
    Route::prefix('common')->group(function () {
        Route::get('info', [Modules\Admin\Http\Controllers\CommonController::class, 'info'])->name('公共模块:INFO接口');
        Route::get('menus', [Modules\Admin\Http\Controllers\CommonController::class, 'menus'])->name('公共模块:菜单栏');
        Route::get('switchSite', [Modules\Admin\Http\Controllers\CommonController::class, 'switchSite'])->name('公共模块:切换站点');
    });

    // User控制器
    Route::prefix('user')->group(function () {
        Route::get('form/{id}', [Modules\Admin\Http\Controllers\UserController::class, 'form'])->name('用户管理:用户单查');
        Route::get('list', [Modules\Admin\Http\Controllers\UserController::class, 'list'])->name('用户管理:用户列表');
        Route::get('info', [Modules\Admin\Http\Controllers\UserController::class, 'UserInfo'])->name('用户管理:个人信息');
        Route::get('options', [Modules\Admin\Http\Controllers\UserController::class, 'options'])->name('用户管理:字典数据');
        Route::post('change-status', [Modules\Admin\Http\Controllers\UserController::class, 'changeStatus'])->name('用户管理:修改状态');
        Route::post('import', [Modules\Admin\Http\Controllers\UserController::class, 'import'])->name('用户管理:用户导入');
        Route::get('export', [Modules\Admin\Http\Controllers\UserController::class, 'export'])->name('用户管理:用户导出');
        Route::get('download', [Modules\Admin\Http\Controllers\UserController::class, 'download'])->name('用户管理:模版下载');
    });



    // Rule控制器
    Route::prefix('rule')->group(function () {
        Route::get('option', [Modules\Admin\Http\Controllers\RuleController::class, 'option'])->name('权限管理:总控权限下拉数据');
        Route::get('option-site', [Modules\Admin\Http\Controllers\RuleController::class, 'optionSite'])->name('权限管理:站点权限下拉数据');
        Route::get('list', [Modules\Admin\Http\Controllers\RuleController::class, 'list'])->name('权限管理:数据列表');
        Route::get('form/{id}', [Modules\Admin\Http\Controllers\RuleController::class, 'form'])->name('权限管理:权限单查');
        Route::get('options', [Modules\Admin\Http\Controllers\RuleController::class, 'options'])->name('权限管理:字典数据');
        Route::post('change-status', [Modules\Admin\Http\Controllers\RuleController::class, 'changeStatus'])->name('权限管理:修改状态');
        Route::get('option-add-rule', [Modules\Admin\Http\Controllers\RuleController::class, 'optionAddRule'])->name('权限管理:新增权限的字典数据');
        Route::post('admin-routes', [Modules\Admin\Http\Controllers\RuleController::class, 'GetAdminRoute'])->name('权限管理:总控模块路由');
    });

    // Role控制器
    Route::prefix('role')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\RoleController::class, 'list'])->name('角色管理:角色列表');
        Route::get('form/{id}', [Modules\Admin\Http\Controllers\RoleController::class, 'form'])->name('角色管理:角色单查');
        Route::get('option', [Modules\Admin\Http\Controllers\RoleController::class, 'option'])->name('角色管理:角色数据下拉');
        Route::post('adminRule', [Modules\Admin\Http\Controllers\RoleController::class, 'adminRule'])->name('角色管理:总控分配权限');
        Route::post('siteRule', [Modules\Admin\Http\Controllers\RoleController::class, 'siteRule'])->name('角色管理:站点分配权限');
        Route::get('options', [Modules\Admin\Http\Controllers\RoleController::class, 'options'])->name('角色管理:字典数据');
        Route::post('change-status', [Modules\Admin\Http\Controllers\RoleController::class, 'changeStatus'])->name('角色管理:修改状态');
        Route::get('adminId/{id}', [Modules\Admin\Http\Controllers\RoleController::class, 'adminId'])->name('角色管理:总控权限数据下拉');
        Route::get('siteId/{id}', [Modules\Admin\Http\Controllers\RoleController::class, 'siteId'])->name('角色管理:站点权限数据下拉');
    });

    // Email控制器
    Route::prefix('email')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\EmailController::class, 'list'])->name('邮箱管理:邮箱列表');
        Route::post('change-status', [Modules\Admin\Http\Controllers\EmailController::class, 'changeStatus'])->name('邮箱管理:状态改变');
        Route::get('option', [Modules\Admin\Http\Controllers\EmailController::class, 'option'])->name('邮箱管理:邮箱下拉数据');
    });


    // EmailScene控制器
    Route::prefix('email-scene')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\EmailSceneController::class, 'list'])->name('发邮场景:发邮列表');
        Route::post('change-status', [Modules\Admin\Http\Controllers\EmailSceneController::class, 'changeStatus'])->name('发邮场景:状态改变');
    });

    // Dictionary控制器
    Route::prefix('dictionary')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\DictionaryController::class, 'list'])->name('字典管理:字典列表');
        Route::get('form/{id}', [Modules\Admin\Http\Controllers\DictionaryController::class, 'form'])->name('字典管理:字典单查');
        Route::get('options', [Modules\Admin\Http\Controllers\DictionaryController::class, 'options'])->name('字典管理:字典数据');
        Route::post('change-status', [Modules\Admin\Http\Controllers\DictionaryController::class, 'changeStatus'])->name('字典管理:修改状态');
    });

    // DictionaryValue控制器
    Route::prefix('dictionary-value')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\DictionaryValueController::class, 'list'])->name('字典管理:字典项列表');
        Route::get('form/{id}', [Modules\Admin\Http\Controllers\DictionaryValueController::class, 'form'])->name('字典管理:字典项单查');
        Route::get('get/{code}', [Modules\Admin\Http\Controllers\DictionaryValueController::class, 'get'])->name('字典管理:字典项查询(KEY)');
        Route::post('change-status', [Modules\Admin\Http\Controllers\DictionaryValueController::class, 'changeStatus'])->name('字典管理:字典项修改状态');
    });

    // Server控制器
    Route::prefix('server')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\ServerController::class, 'list'])->name('服务器管理:服务器列表');
        Route::post('change-status', [Modules\Admin\Http\Controllers\ServerController::class, 'changeStatus'])->name('服务器管理:状态修改');
    });

    // System控制器
    Route::prefix('system')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\SystemController::class, 'list'])->name('平台字段:父级列表');
        Route::get('form/{id}', [Modules\Admin\Http\Controllers\SystemController::class, 'form'])->name('平台字段:父级单查');
        Route::post('change-status', [Modules\Admin\Http\Controllers\SystemController::class, 'changeStatus'])->name('平台字段:父级修改状态');
        Route::get('option', [Modules\Admin\Http\Controllers\SystemController::class, 'option'])->name('平台字段:父级下拉数据');
        Route::get('value-list/{parent_id}', [Modules\Admin\Http\Controllers\SystemController::class, 'valueList'])->name('平台字段:某个父级下的子级列表');
    });
    Route::prefix('system-value')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\SystemController::class, 'systemValueList'])->name('平台字段:全部子级列表');
        Route::post('change-status', [Modules\Admin\Http\Controllers\SystemController::class, 'valueChangeStatus'])->name('平台字段:子级修改状态');
        Route::get('form/{id}', [Modules\Admin\Http\Controllers\SystemController::class, 'formValue'])->name('平台字段:子级单查');
        Route::post('change-hidden', [Modules\Admin\Http\Controllers\SystemController::class, 'valueChangeHidden'])->name('平台字段:子级显示状态');
    });


    // Department控制器
    Route::prefix('department')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\DepartmentController::class, 'list'])->name('部门管理:部门列表');
        Route::get('form/{id}', [Modules\Admin\Http\Controllers\DepartmentController::class, 'form'])->name('部门管理:部门单查');
        Route::get('options', [Modules\Admin\Http\Controllers\DepartmentController::class, 'options'])->name('部门管理:字典数据');
        Route::post('change-status', [Modules\Admin\Http\Controllers\DepartmentController::class, 'changeStatus'])->name('部门管理:修改状态');
    });


    // Database控制器
    Route::prefix('database')->group(function () {
        Route::post('change-status', [Modules\Admin\Http\Controllers\DatabaseController::class, 'changeStatus'])->name('数据库管理:状态修改');
    });

    // Site控制器
    Route::prefix('site')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\SiteController::class, 'list'])->name('站点管理:站点列表');
        Route::get('user-option', [Modules\Admin\Http\Controllers\SiteController::class, 'UserOption'])->name('站点管理:用户站点下拉数据');
        Route::get('option', [Modules\Admin\Http\Controllers\SiteController::class, 'option'])->name('站点管理:站点列表下拉数据');
        Route::get('search-droplist', [Modules\Admin\Http\Controllers\SiteController::class, 'searchDroplist'])->name('站点管理:搜索下拉列表数据');
        Route::post('change-status',[Modules\Admin\Http\Controllers\SiteController::class, 'changeStatus'])->name('站点管理:状态修改');
        // Route::post('message', [Modules\Admin\Http\Controllers\SiteController::class, 'message'])->name('站点管理:站点测试');
        // Route::post('callback-results', [Modules\Admin\Http\Controllers\SiteController::class, 'callbackResults'])->name('站点管理:站点更新结果');
        // Route::get('get-catch-git-status', [Modules\Admin\Http\Controllers\SiteController::class, 'getCatchGitStatus'])->name('站点管理:返回更新结果');
    });

    // EmailLog控制器
    Route::prefix('email-log')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\EmailLogController::class, 'list'])->name('邮箱日志:日志列表');
        Route::get('option', [Modules\Admin\Http\Controllers\EmailLogController::class, 'option'])->name('邮箱日志:字典数据');
    });

    // OperationLogController 控制器
    Route::prefix('operation-log')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\OperationLogController::class, 'list'])->name('操作日志:数据列表');
        Route::post('destroy', [Modules\Admin\Http\Controllers\OperationLogController::class, 'destroy'])->name('操作日志:删除操作');
        Route::get('options', [Modules\Admin\Http\Controllers\OperationLogController::class, 'options'])->name('操作日志:字典数据');
    });

    // SiteUpdateLogController 控制器
    Route::prefix('site-update-log')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\SiteUpdateLogController::class, 'list'])->name('升级日志:日志列表');
        Route::get('search-droplist', [Modules\Admin\Http\Controllers\SiteUpdateLogController::class, 'searchDroplist'])->name('升级日志:搜索下拉列表数据');
    });

    // Region 控制器
    Route::prefix('region')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\RegionController::class, 'list'])->name('地区管理:地区列表');
        Route::get('option', [Modules\Admin\Http\Controllers\RegionController::class, 'option'])->name('地区管理:地区列表下拉列表数据');
        Route::get('search-droplist', [Modules\Admin\Http\Controllers\RegionController::class, 'searchDroplist'])->name('地区管理:搜索下拉列表数据');
        Route::post('change-status',[Modules\Admin\Http\Controllers\RegionController::class, 'changeStatus'])->name('地区管理:状态修改');
    });

    // Language 控制器
    Route::prefix('language')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\LanguageController::class, 'list'])->name('语言管理:语言列表');
        Route::get('option', [Modules\Admin\Http\Controllers\LanguageController::class, 'option'])->name('语言管理:语言列表下拉列表数据');
        Route::get('search-droplist', [Modules\Admin\Http\Controllers\LanguageController::class, 'searchDroplist'])->name('语言管理:搜索下拉列表数据');
        Route::post('change-status',[Modules\Admin\Http\Controllers\LanguageController::class, 'changeStatus'])->name('语言管理:状态修改');
    });


    // Publisher 控制器
    Route::prefix('publisher')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\PublisherController::class, 'list'])->name('出版商管理:出版商列表');
        Route::get('option', [Modules\Admin\Http\Controllers\PublisherController::class, 'option'])->name('出版商管理:出版商列表下拉列表数据');
        Route::get('search-droplist', [Modules\Admin\Http\Controllers\PublisherController::class, 'searchDroplist'])->name('出版商管理:搜索下拉列表数据');
        Route::post('change-status',[Modules\Admin\Http\Controllers\PublisherController::class, 'changeStatus'])->name('出版商管理:状态修改');
    });

    // PriceEdition 控制器
    Route::prefix('price-edition')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\PriceEditionController::class, 'list'])->name('价格版本管理:价格版本列表');
        Route::get('option', [Modules\Admin\Http\Controllers\PriceEditionController::class, 'option'])->name('价格版本管理:价格版本列表下拉列表数据');
        Route::get('search-droplist', [Modules\Admin\Http\Controllers\PriceEditionController::class, 'searchDroplist'])->name('价格版本管理:搜索下拉列表数据');
        Route::post('change-status',[Modules\Admin\Http\Controllers\PriceEditionController::class, 'changeStatus'])->name('价格版本管理:状态修改');
    });

    // FileManagement 控制器
    Route::prefix('file-management')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\FileManagement::class, 'FileList'])->name('文件管理:文件列表');
        Route::post('create_dir', [Modules\Admin\Http\Controllers\FileManagement::class, 'CreateDir'])->name('文件管理:文件创建');
        Route::post('rename', [Modules\Admin\Http\Controllers\FileManagement::class, 'rename'])->name('文件管理:文件夹重命名');
        Route::post('delete', [Modules\Admin\Http\Controllers\FileManagement::class, 'delete'])->name('文件管理:文件夹删除');
        Route::post('copy', [Modules\Admin\Http\Controllers\FileManagement::class, 'CopyAndMove'])->name('文件管理:文件夹复制');
        Route::post('move', [Modules\Admin\Http\Controllers\FileManagement::class, 'CopyAndMove'])->name('文件管理:文件夹移动');
        Route::post('cmpress', [Modules\Admin\Http\Controllers\FileManagement::class, 'cmpress'])->name('文件管理:文件夹压缩');
        Route::post('uploads', [Modules\Admin\Http\Controllers\FileManagement::class, 'uploads'])->name('文件管理:文件上传');
    });
});


/** 需要登陆并且需要验证权限的路由 */
Route::middleware([
    'api',
    JwtMiddleware::class, // JWT验证中间件
    'language', // 语言中间件
    // 'rule' // 权限验证中间件
])->prefix('admin')->group(function () {

    // User控制器
    Route::prefix('user')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\UserController::class, 'store'])->name('用户管理:用户新增');
        Route::post('destroy', [Modules\Admin\Http\Controllers\UserController::class, 'destroy'])->name('用户管理:用户删除');
        Route::post('update', [Modules\Admin\Http\Controllers\UserController::class, 'update'])->name('用户管理:用户编辑');
    });


    // Rule控制器
    Route::prefix('rule')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\RuleController::class, 'store'])->name('权限管理:权限新增');
        Route::post('destroy', [Modules\Admin\Http\Controllers\RuleController::class, 'destroy'])->name('权限管理:权限删除');
        Route::post('update', [Modules\Admin\Http\Controllers\RuleController::class, 'update'])->name('权限管理:权限编辑');
    });

    // Role控制器
    Route::prefix('role')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\RoleController::class, 'store'])->name('角色管理:角色新增');
        Route::post('update', [Modules\Admin\Http\Controllers\RoleController::class, 'update'])->name('角色管理:角色编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\RoleController::class, 'destroy'])->name('角色管理:角色删除');
    });


    // Email控制器
    Route::prefix('email')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\EmailController::class, 'store'])->name('邮箱管理:邮箱新增');
        Route::post('destroy', [Modules\Admin\Http\Controllers\EmailController::class, 'destroy'])->name('邮箱管理:邮箱删除');
        Route::post('update', [Modules\Admin\Http\Controllers\EmailController::class, 'update'])->name('邮箱管理:邮箱编辑');
    });

    // EmailScene控制器
    Route::prefix('email-scene')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\EmailSceneController::class, 'store'])->name('发邮场景:发邮新增');
        Route::post('update', [Modules\Admin\Http\Controllers\EmailSceneController::class, 'update'])->name('发邮场景:发邮编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\EmailSceneController::class, 'destroy'])->name('发邮场景:发邮删除');
    });


    // Dictionary控制器
    Route::prefix('dictionary')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\DictionaryController::class, 'store'])->name('字典管理:字典新增');
        Route::post('update', [Modules\Admin\Http\Controllers\DictionaryController::class, 'update'])->name('字典管理:字典编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\DictionaryController::class, 'destroy'])->name('字典管理:字典删除');
    });

    // DictionaryValue控制器
    Route::prefix('dictionary-value')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\DictionaryValueController::class, 'store'])->name('字典管理:字典项新增');
        Route::post('update', [Modules\Admin\Http\Controllers\DictionaryValueController::class, 'update'])->name('字典管理:字典项编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\DictionaryValueController::class, 'destroy'])->name('字典管理:字典项删除');
    });

    // Database控制器
    Route::prefix('database')->group(function () {
        Route::get('list', [Modules\Admin\Http\Controllers\DatabaseController::class, 'list'])->name('数据库管理:列表');
        Route::post('store', [Modules\Admin\Http\Controllers\DatabaseController::class, 'store'])->name('数据库管理:新增');
        Route::post('update', [Modules\Admin\Http\Controllers\DatabaseController::class, 'update'])->name('数据库管理:编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\DatabaseController::class, 'destroy'])->name('数据库管理:删除');
    });

    // Server控制器
    Route::prefix('server')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\ServerController::class, 'store'])->name('服务器管理:新增');
        Route::post('update', [Modules\Admin\Http\Controllers\ServerController::class, 'update'])->name('服务器管理:编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\ServerController::class, 'destroy'])->name('服务器管理:删除');
    });

    // System控制器
    Route::prefix('system')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\SystemController::class, 'store'])->name('平台字段:父级新增');
        Route::post('update', [Modules\Admin\Http\Controllers\SystemController::class, 'update'])->name('平台字段:父级编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\SystemController::class, 'destroy'])->name('平台字段:父级删除');
    });

    Route::prefix('system-value')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\SystemController::class, 'systemValueStore'])->name('平台字段:子级新增');
        Route::post('update', [Modules\Admin\Http\Controllers\SystemController::class, 'systemValueUpdate'])->name('平台字段:子级编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\SystemController::class, 'systemValueDestroy'])->name('平台字段:子级删除');
    });

    // Department控制器
    Route::prefix('department')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\DepartmentController::class, 'store'])->name('部门管理:部门新增');
        Route::post('update', [Modules\Admin\Http\Controllers\DepartmentController::class, 'update'])->name('部门管理:部门编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\DepartmentController::class, 'destroy'])->name('部门管理:部门删除');
    });

    // EmailLog控制器
    Route::prefix('email-log')->group(function () {
        Route::post('destroy', [Modules\Admin\Http\Controllers\EmailLogController::class, 'destroy'])->name('邮箱日志:日志删除');
    });


    // Site 控制器
    Route::prefix('site')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\SiteController::class, 'store'])->name('站点管理:站点新增');
        Route::post('update', [Modules\Admin\Http\Controllers\SiteController::class, 'update'])->name('站点管理:站点编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\SiteController::class, 'destroy'])->name('站点管理:站点删除');
        Route::post('create-site-to-remote-server', [Modules\Admin\Http\Controllers\SiteController::class, 'createSiteToRemoteServer'])->name('站点管理:初始化站点');
        Route::post('init-database', [Modules\Admin\Http\Controllers\SiteController::class, 'initDatabase'])->name('站点管理:初始化数据库');
        Route::post('available-upgrade', [Modules\Admin\Http\Controllers\SiteController::class, 'availableUpgrade'])->name('站点管理:git可用更新');
        Route::post('update-site-to-remote-server', [Modules\Admin\Http\Controllers\SiteController::class, 'updateSiteToRemoteServer'])->name('站点管理:站点升级');
        Route::post('commit-history', [Modules\Admin\Http\Controllers\SiteController::class, 'CommitHistory'])->name('站点管理:git提交记录历史');
        Route::post('rollback-code', [Modules\Admin\Http\Controllers\SiteController::class, 'rollbackCode'])->name('站点管理:git版本回退');
        Route::post('set-header-title', [Modules\Admin\Http\Controllers\SiteController::class, 'setHeaderTitle'])->name('站点管理:设置自定义表头');
        
    });
    
    // SiteUpdateLog 控制器
    Route::prefix('site-update-log')->group(function () {
        Route::post('destroy', [Modules\Admin\Http\Controllers\SiteController::class, 'destroy'])->name('升级日志:日志删除');
        
    });

    // Region 控制器
    Route::prefix('region')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\RegionController::class, 'store'])->name('地区管理:地区新增');
        Route::post('update', [Modules\Admin\Http\Controllers\RegionController::class, 'update'])->name('地区管理:地区编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\RegionController::class, 'destroy'])->name('地区管理:地区删除');
    });

    // Language 控制器
    Route::prefix('language')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\LanguageController::class, 'store'])->name('语言管理:语言新增');
        Route::post('update', [Modules\Admin\Http\Controllers\LanguageController::class, 'update'])->name('语言管理:语言编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\LanguageController::class, 'destroy'])->name('语言管理:语言删除');
    });

    // Publisher 控制器
    Route::prefix('publisher')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\PublisherController::class, 'store'])->name('出版商管理:出版商新增');
        Route::post('update', [Modules\Admin\Http\Controllers\PublisherController::class, 'update'])->name('出版商管理:出版商编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\PublisherController::class, 'destroy'])->name('出版商管理:出版商删除');
        Route::post('upload-logo', [Modules\Admin\Http\Controllers\PublisherController::class, 'uploadLogo'])->name('出版商管理:上传logo');
    });

    // PriceEdition 控制器
    Route::prefix('price-edition')->group(function () {
        Route::post('store', [Modules\Admin\Http\Controllers\PriceEditionController::class, 'store'])->name('价格版本管理:价格版本新增');
        Route::post('update', [Modules\Admin\Http\Controllers\PriceEditionController::class, 'update'])->name('价格版本管理:价格版本编辑');
        Route::post('destroy', [Modules\Admin\Http\Controllers\PriceEditionController::class, 'destroy'])->name('价格版本管理:价格版本删除');
        Route::post('set-header-title', [Modules\Admin\Http\Controllers\PriceEditionController::class, 'setHeaderTitle'])->name('价格版本管理:设置自定义表头');
    });

});

/** 不需要登陆也不需要验证权限的路由 */
Route::middleware([
    'api',
    'language' // 语言中间件
])->group(function () {
    Route::get('admin/department/option', 'DepartmentController@option')->name('部门option');
    // Position控制器
    Route::get('admin/position/list', 'PositionController@list')->name('职位列表');
    Route::get('admin/country/get-country', 'CountryController@getCountry')->name('国家列表');
    Route::get('admin/common/get-status', 'CommonController@getStatus')->name('获取状态 未知');
    Route::get('admin/common/filters', 'CommonController@filters')->name('公共数据'); // 公共的列表表头和下拉数据

    Route::get('admin/test/test', 'TestController@TestPush')->name('测试接口');
    Route::get('admin/test/test01', 'TestController@Test01')->name('测试接口');
    Route::get('admin/test/test02', 'TestController@Test02')->name('测试接口');

    Route::get('baba', [\Modules\Admin\Http\Controllers\CronTask\DepartmentController::class, 'test'])->name('测试接口');
    Route::get('admin/database/phpmyadmin/{id}', [Modules\Admin\Http\Controllers\DatabaseController::class, 'HrefMyAdmin'])->name('数据库管理:打开PHPMYADMIN');
    Route::get('admin/file-management/download', [Modules\Admin\Http\Controllers\FileManagement::class, 'download'])->name('文件管理:文件下载');

});

// 暂时测试路由
Route::get('/phpmyadmin', function () {
    return view('phpmyadmin');
});
