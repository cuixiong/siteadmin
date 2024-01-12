<?php

use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;
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
Route::middleware([
    'api',
    InitializeTenancyByRequestData::class,
    JwtMiddleware::class,
    'language' // 语言中间件
])->prefix('site')->group(function () {
    Route::get('site/select', [\Modules\Site\Http\Controllers\SiteController::class, 'select']);
    Route::get('site/update', [\Modules\Site\Http\Controllers\SiteController::class, 'update']);
    Route::get('site/insert', [\Modules\Site\Http\Controllers\SiteController::class, 'insert']);
    Route::get('site/delete', [\Modules\Site\Http\Controllers\SiteController::class, 'delete']);

    // FileManagement 控制器
    Route::prefix('file-management')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\FileManagement::class, 'FileList'])->name('站点端:文件管理:文件列表');
        Route::post('create_dir', [Modules\Site\Http\Controllers\FileManagement::class, 'CreateDir'])->name('站点端:文件管理:文件创建');
        Route::post('rename', [Modules\Site\Http\Controllers\FileManagement::class, 'rename'])->name('站点端:文件管理:文件夹重命名');
        Route::post('delete', [Modules\Site\Http\Controllers\FileManagement::class, 'delete'])->name('站点端:文件管理:文件夹删除');
        Route::post('copy', [Modules\Site\Http\Controllers\FileManagement::class, 'CopyAndMove'])->name('站点端:文件管理:文件夹复制');
        Route::post('move', [Modules\Site\Http\Controllers\FileManagement::class, 'CopyAndMove'])->name('站点端:文件管理:文件夹移动');
        Route::post('cmpress', [Modules\Site\Http\Controllers\FileManagement::class, 'cmpress'])->name('站点端:文件管理:文件夹压缩');
        Route::post('uploads', [Modules\Site\Http\Controllers\FileManagement::class, 'uploads'])->name('站点端:文件管理:文件上传');
        Route::get('dir_list', [Modules\Site\Http\Controllers\FileManagement::class, 'DirList'])->name('站点端:文件管理:文件夹列表(下拉)');
        Route::post('dir_size', [Modules\Site\Http\Controllers\FileManagement::class, 'DirSize'])->name('站点端:文件管理:文件夹大小计算');
        Route::post('unzip', [Modules\Site\Http\Controllers\FileManagement::class, 'unzip'])->name('站点端:文件管理:文件解压');
        Route::post('force-file-overwrite', [Modules\Site\Http\Controllers\FileManagement::class, 'ForceFileOverwrite'])->name('文件管理:强制覆盖文件');
    });


    // Region 控制器
    Route::prefix('region')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\RegionController::class, 'list'])->name('地区管理:地区列表');
        Route::get('option', [Modules\Site\Http\Controllers\RegionController::class, 'option'])->name('地区管理:下拉列表数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\RegionController::class, 'searchDroplist'])->name('地区管理:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\RegionController::class, 'changeStatus'])->name('地区管理:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\RegionController::class, 'changeSort'])->name('地区管理:排序修改');

        Route::post('store', [Modules\Site\Http\Controllers\RegionController::class, 'store'])->name('地区管理:新增地区');
        Route::post('update', [Modules\Site\Http\Controllers\RegionController::class, 'update'])->name('地区管理:修改地区');
        Route::post('destroy', [Modules\Site\Http\Controllers\RegionController::class, 'destroy'])->name('地区管理:删除地区');
    });

    
    // Country 控制器
    Route::prefix('country')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\CountryController::class, 'list'])->name('国家管理:地区列表');
        Route::get('option', [Modules\Site\Http\Controllers\CountryController::class, 'option'])->name('国家管理:下拉列表数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\CountryController::class, 'searchDroplist'])->name('国家管理:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\CountryController::class, 'changeStatus'])->name('国家管理:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\CountryController::class, 'changeSort'])->name('国家管理:排序修改');

        Route::post('store', [Modules\Site\Http\Controllers\CountryController::class, 'store'])->name('国家管理:新增地区');
        Route::post('update', [Modules\Site\Http\Controllers\CountryController::class, 'update'])->name('国家管理:修改地区');
        Route::post('destroy', [Modules\Site\Http\Controllers\CountryController::class, 'destroy'])->name('国家管理:删除地区');
    });


    // Products 控制器
    Route::prefix('products')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ProductsController::class, 'list'])->name('报告管理:报告列表');
        Route::get('option', [Modules\Site\Http\Controllers\ProductsController::class, 'option'])->name('报告管理:下拉列表数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\ProductsController::class, 'searchDroplist'])->name('报告管理:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\ProductsController::class, 'changeStatus'])->name('报告管理:状态修改');
        Route::post('change-hot', [Modules\Site\Http\Controllers\ProductsController::class, 'changeHot'])->name('报告管理:热门状态修改');
        Route::post('change-recommend', [Modules\Site\Http\Controllers\ProductsController::class, 'changeRecommend'])->name('报告管理:推荐状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\ProductsController::class, 'changeSort'])->name('报告管理:排序修改');
        Route::post('change-price', [Modules\Site\Http\Controllers\ProductsController::class, 'changePrice'])->name('报告管理:基础价修改');
        Route::post('set-header-title', [Modules\Site\Http\Controllers\ProductsController::class, 'setHeaderTitle'])->name('报告管理:设置自定义表头');


        Route::post('store', [Modules\Site\Http\Controllers\ProductsController::class, 'store'])->name('报告管理:新增报告');
        Route::get('is-exist', [Modules\Site\Http\Controllers\ProductsController::class, 'isExist'])->name('报告管理:报告是否存在');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\ProductsController::class, 'form'])->name('报告管理:报告单查');
        Route::post('update', [Modules\Site\Http\Controllers\ProductsController::class, 'update'])->name('报告管理:修改报告');
        Route::post('destroy', [Modules\Site\Http\Controllers\ProductsController::class, 'destroy'])->name('报告管理:删除报告');
        Route::post('discount', [Modules\Site\Http\Controllers\ProductsController::class, 'discount'])->name('报告管理:设置折扣');
        Route::post('export', [Modules\Site\Http\Controllers\ProductsController::class, 'export'])->name('报告管理:批量导出');
        Route::post('export-process', [Modules\Site\Http\Controllers\ProductsController::class, 'exportProcess'])->name('报告管理:导出进度');
        Route::post('export-file-download', [Modules\Site\Http\Controllers\ProductsController::class, 'exportFileDownload'])->name('报告管理:下载导出文件');

        Route::get('batch-update-param', [Modules\Site\Http\Controllers\ProductsController::class, 'batchUpdateParam'])->name('报告管理:批量修改参数');
        Route::get('batch-update-option', [Modules\Site\Http\Controllers\ProductsController::class, 'batchUpdateOption'])->name('报告管理:批量修改参数子项');
        Route::post('batch-update', [Modules\Site\Http\Controllers\ProductsController::class, 'batchUpdate'])->name('报告管理:批量修改');
        Route::post('batch-delete', [Modules\Site\Http\Controllers\ProductsController::class, 'batchDelete'])->name('报告管理:批量删除');

    });
    
    // ProductsUploadLog 控制器
    Route::prefix('products-upload-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ProductsUploadLogController::class, 'list'])->name('上传记录:记录列表');
        Route::get('get-publisher', [Modules\Site\Http\Controllers\ProductsUploadLogController::class, 'getPublisher'])->name('上传记录:出版商数据');

        Route::post('destroy', [Modules\Site\Http\Controllers\ProductsUploadLogController::class, 'destroy'])->name('上传记录:删除记录');
        Route::post('upload-products', [Modules\Site\Http\Controllers\ProductsUploadLogController::class, 'uploadProducts'])->name('上传记录:上传报告');
        Route::post('upload-process', [Modules\Site\Http\Controllers\ProductsUploadLogController::class, 'uploadProcess'])->name('上传记录:上传进度');
        Route::get('example-file', [Modules\Site\Http\Controllers\ProductsUploadLogController::class, 'exampleFile'])->name('上传记录:示例文件');
        
    });
    
    // ProductsExportLog 控制器
    Route::prefix('products-export-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ProductsExportLogController::class, 'list'])->name('导出记录:导出列表');
        Route::post('destroy', [Modules\Site\Http\Controllers\ProductsExportLogController::class, 'destroy'])->name('导出记录:删除记录');
        
    });

    // ProductsCategory 控制器
    Route::prefix('products-category')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'list'])->name('报告分类:分类列表');
        Route::get('option', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'option'])->name('报告分类:下拉列表数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'searchDroplist'])->name('报告分类:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'changeStatus'])->name('报告分类:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'changeSort'])->name('报告分类:排序修改');
        Route::post('set-header-title', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'setHeaderTitle'])->name('报告分类:设置自定义表头');
        Route::get('get-category', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'getCategory'])->name('报告分类:获取某层分类');
        

        Route::post('store', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'store'])->name('报告分类:新增分类');
        Route::post('update', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'update'])->name('报告分类:修改分类');
        Route::post('destroy', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'destroy'])->name('报告分类:删除分类');
        Route::post('discount', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'discount'])->name('报告分类:设置折扣');
    });
    
    // ProductsExcelField 控制器
    Route::prefix('products-excel-field')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ProductsExcelFieldController::class, 'list'])->name('报告字段:字段列表');
        Route::get('option', [Modules\Site\Http\Controllers\ProductsExcelFieldController::class, 'option'])->name('报告字段:下拉列表数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\ProductsExcelFieldController::class, 'searchDroplist'])->name('报告字段:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\ProductsExcelFieldController::class, 'changeStatus'])->name('报告字段:状态修改');
        // Route::post('change-sort', [Modules\Site\Http\Controllers\ProductsExcelFieldController::class, 'changeSort'])->name('报告字段:排序修改');
        Route::post('reset-sort', [Modules\Site\Http\Controllers\ProductsExcelFieldController::class, 'resetSort'])->name('报告字段:调整排序');

        Route::post('store', [Modules\Site\Http\Controllers\ProductsExcelFieldController::class, 'store'])->name('报告字段:新增字段');
        Route::post('update', [Modules\Site\Http\Controllers\ProductsExcelFieldController::class, 'update'])->name('报告字段:修改字段');
        Route::post('destroy', [Modules\Site\Http\Controllers\ProductsExcelFieldController::class, 'destroy'])->name('报告字段:删除字段');

    });
    
    // Order 控制器
    Route::prefix('order')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\OrderController::class, 'list'])->name('订单管理:订单列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\OrderController::class, 'searchDroplist'])->name('订单管理:订单下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\OrderController::class, 'changeStatus'])->name('订单管理:状态修改');

        Route::post('update', [Modules\Site\Http\Controllers\OrderController::class, 'update'])->name('订单管理:修改订单');
        Route::post('destroy', [Modules\Site\Http\Controllers\OrderController::class, 'destroy'])->name('订单管理:删除订单');

    });

    // ShopCart 控制器
    Route::prefix('shop-cart')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ShopCartController::class, 'list'])->name('购物车:订单列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\ShopCartController::class, 'searchDroplist'])->name('购物车:下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\ShopCartController::class, 'changeStatus'])->name('购物车:状态修改');

        Route::post('store', [Modules\Site\Http\Controllers\ShopCartController::class, 'store'])->name('购物车:新增购物车');
        Route::post('update', [Modules\Site\Http\Controllers\ShopCartController::class, 'update'])->name('购物车:修改购物车');
        Route::post('destroy', [Modules\Site\Http\Controllers\ShopCartController::class, 'destroy'])->name('购物车:删除购物车');

    });
    
    // Invoice 控制器
    Route::prefix('invoice')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\InvoiceController::class, 'list'])->name('发票管理:发票列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\InvoiceController::class, 'searchDroplist'])->name('发票管理:下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\InvoiceController::class, 'changeStatus'])->name('发票管理:发票状态修改');

        Route::post('store', [Modules\Site\Http\Controllers\InvoiceController::class, 'store'])->name('发票管理:新增发票');
        Route::post('update', [Modules\Site\Http\Controllers\InvoiceController::class, 'update'])->name('发票管理:修改发票');
        Route::post('destroy', [Modules\Site\Http\Controllers\InvoiceController::class, 'destroy'])->name('发票管理:删除发票');

    });
    
    // Coupon 控制器
    Route::prefix('coupon')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\CouponController::class, 'list'])->name('优惠券:字段列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\CouponController::class, 'searchDroplist'])->name('优惠券:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\CouponController::class, 'changeStatus'])->name('优惠券:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\CouponController::class, 'changeSort'])->name('优惠券:排序修改');

        Route::post('store', [Modules\Site\Http\Controllers\CouponController::class, 'store'])->name('优惠券:新增优惠券');
        Route::post('update', [Modules\Site\Http\Controllers\CouponController::class, 'update'])->name('优惠券:修改优惠券');
        Route::post('destroy', [Modules\Site\Http\Controllers\CouponController::class, 'destroy'])->name('优惠券:删除优惠券');

    });

    // SearchRank 控制器
    Route::prefix('search-rank')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\SearchRankController::class, 'list'])->name('搜索排行:搜索列表');
        Route::get('option', [Modules\Site\Http\Controllers\SearchRankController::class, 'option'])->name('搜索排行:下拉列表数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\SearchRankController::class, 'searchDroplist'])->name('搜索排行:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\SearchRankController::class, 'changeStatus'])->name('搜索排行:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\SearchRankController::class, 'changeSort'])->name('搜索排行:排序修改');

        Route::post('store', [Modules\Site\Http\Controllers\SearchRankController::class, 'store'])->name('搜索排行:新增字段');
        Route::post('update', [Modules\Site\Http\Controllers\SearchRankController::class, 'update'])->name('搜索排行:修改字段');
        Route::post('destroy', [Modules\Site\Http\Controllers\SearchRankController::class, 'destroy'])->name('搜索排行:删除字段');

    });

    // EmailLog控制器
    Route::prefix('email-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\EmailLogController::class, 'list'])->name('邮箱日志:日志列表');
        Route::get('option', [Modules\Site\Http\Controllers\EmailLogController::class, 'option'])->name('邮箱日志:字典数据');
    });

    // Email控制器
    Route::prefix('email')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\EmailController::class, 'list'])->name('邮箱管理:邮箱列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\EmailController::class, 'changeStatus'])->name('邮箱管理:状态改变');
        Route::get('option', [Modules\Site\Http\Controllers\EmailController::class, 'option'])->name('邮箱管理:邮箱下拉数据');
        Route::post('store', [Modules\Site\Http\Controllers\EmailController::class, 'store'])->name('邮箱管理:邮箱新增');
        Route::post('destroy', [Modules\Site\Http\Controllers\EmailController::class, 'destroy'])->name('邮箱管理:邮箱删除');
        Route::post('update', [Modules\Site\Http\Controllers\EmailController::class, 'update'])->name('邮箱管理:邮箱编辑');
    });

    // EmailScene控制器
    Route::prefix('email-scene')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\EmailSceneController::class, 'list'])->name('发邮场景:发邮列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\EmailSceneController::class, 'changeStatus'])->name('发邮场景:状态改变');
        Route::post('store', [Modules\Site\Http\Controllers\EmailSceneController::class, 'store'])->name('发邮场景:发邮新增');
        Route::post('update', [Modules\Site\Http\Controllers\EmailSceneController::class, 'update'])->name('发邮场景:发邮编辑');
        Route::post('destroy', [Modules\Site\Http\Controllers\EmailSceneController::class, 'destroy'])->name('发邮场景:发邮删除');
        Route::get('options', [Modules\Site\Http\Controllers\EmailSceneController::class, 'options'])->name('发邮场景:字典数据');
    });

    // Menu控制器
    Route::prefix('menu')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\MenuController::class, 'list'])->name('导航菜单:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\MenuController::class, 'changeStatus'])->name('导航菜单:状态改变');
        Route::post('store', [Modules\Site\Http\Controllers\MenuController::class, 'store'])->name('导航菜单:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\MenuController::class, 'update'])->name('导航菜单:数据编辑');
        Route::post('destroy', [Modules\Site\Http\Controllers\MenuController::class, 'destroy'])->name('导航菜单:数据删除');
        Route::get('option', [Modules\Site\Http\Controllers\MenuController::class, 'option'])->name('导航菜单:下拉数据');
        Route::get('options', [Modules\Site\Http\Controllers\MenuController::class, 'options'])->name('导航菜单:字典数据');
        Route::get('is-single', [Modules\Site\Http\Controllers\MenuController::class, 'isSingle'])->name('导航菜单:是否单页修改');
    });

    // System控制器
    Route::prefix('system')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\SystemController::class, 'store'])->name('网点配置:父级新增');
        Route::post('update', [Modules\Site\Http\Controllers\SystemController::class, 'update'])->name('网点配置:父级编辑');
        Route::post('destroy', [Modules\Site\Http\Controllers\SystemController::class, 'destroy'])->name('网点配置:父级删除');
        Route::get('list', [Modules\Site\Http\Controllers\SystemController::class, 'list'])->name('网点配置:父级列表');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\SystemController::class, 'form'])->name('网点配置:父级单查');
        Route::post('change-status', [Modules\Site\Http\Controllers\SystemController::class, 'changeStatus'])->name('网点配置:父级修改状态');
        Route::get('option', [Modules\Site\Http\Controllers\SystemController::class, 'option'])->name('网点配置:父级下拉数据');
        Route::get('value-list/{parent_id}', [Modules\Site\Http\Controllers\SystemController::class, 'valueList'])->name('网点配置:某个父级下的子级列表');
    });

    // System控制器
    Route::prefix('system-value')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\SystemController::class, 'systemValueStore'])->name('网点配置:子级新增');
        Route::post('update', [Modules\Site\Http\Controllers\SystemController::class, 'systemValueUpdate'])->name('网点配置:子级编辑');
        Route::post('destroy', [Modules\Site\Http\Controllers\SystemController::class, 'systemValueDestroy'])->name('网点配置:子级删除');
        Route::get('list', [Modules\Site\Http\Controllers\SystemController::class, 'systemValueList'])->name('网点配置:全部子级列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\SystemController::class, 'valueChangeStatus'])->name('网点配置:子级修改状态');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\SystemController::class, 'formValue'])->name('网点配置:子级单查');
        Route::post('change-hidden', [Modules\Site\Http\Controllers\SystemController::class, 'valueChangeHidden'])->name('网点配置:子级显示状态');
    });
    // User控制器
    Route::prefix('user')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\UserController::class, 'store'])->name('用户列表:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\UserController::class, 'update'])->name('用户列表:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\UserController::class, 'destroy'])->name('用户列表:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\UserController::class, 'list'])->name('用户列表:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\UserController::class, 'changeStatus'])->name('用户列表:状态修改');
        Route::get('options', [Modules\Site\Http\Controllers\UserController::class, 'options'])->name('用户列表:字典数据');
    });
    // Pay控制器
    Route::prefix('pay')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PayController::class, 'store'])->name('支付列表:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PayController::class, 'update'])->name('支付列表:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\PayController::class, 'destroy'])->name('支付列表:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\PayController::class, 'list'])->name('支付列表:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PayController::class, 'changeStatus'])->name('支付列表:状态修改');
    });
    // Applyfor控制器
    Route::prefix('applyfor')->group(function () {
        Route::post('update', [Modules\Site\Http\Controllers\ApplyforController::class, 'update'])->name('申请样本:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\ApplyforController::class, 'destroy'])->name('申请样本:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\ApplyforController::class, 'list'])->name('申请样本:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\ApplyforController::class, 'changeStatus'])->name('申请样本:状态修改');
    });
    // MessageCategory控制器
    Route::prefix('message-category')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PayController::class, 'store'])->name('留言分类:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\ApplyforController::class, 'update'])->name('留言分类:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\ApplyforController::class, 'destroy'])->name('留言分类:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\ApplyforController::class, 'list'])->name('留言分类:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\ApplyforController::class, 'changeStatus'])->name('留言分类:状态修改');
    });
    // Plate控制器
    Route::prefix('plate')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PlateController::class, 'store'])->name('页面板块:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PlateController::class, 'update'])->name('页面板块:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\PlateController::class, 'destroy'])->name('页面板块:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\PlateController::class, 'list'])->name('页面板块:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PlateController::class, 'changeStatus'])->name('页面板块:状态修改');
    });
    // Plate-Value控制器
    Route::prefix('plate-value')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PlateValueController::class, 'store'])->name('页面板块子级:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PlateValueController::class, 'update'])->name('页面板块子级:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\PlateValueController::class, 'destroy'])->name('页面板块子级:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\PlateValueController::class, 'list'])->name('页面板块子级:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PlateValueController::class, 'changeStatus'])->name('页面板块子级:状态修改');
    });
    // SinglePage控制器
    Route::prefix('single-page')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\SinglePageController::class, 'store'])->name('单页管理:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\SinglePageController::class, 'update'])->name('单页管理:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\SinglePageController::class, 'destroy'])->name('单页管理:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\SinglePageController::class, 'list'])->name('单页管理:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\SinglePageController::class, 'changeStatus'])->name('单页管理:状态修改');
    });
    // Authority控制器
    Route::prefix('authority')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\AuthorityController::class, 'store'])->name('权威引用:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\AuthorityController::class, 'update'])->name('权威引用:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\AuthorityController::class, 'destroy'])->name('权威引用:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\AuthorityController::class, 'list'])->name('权威引用:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\AuthorityController::class, 'changeStatus'])->name('权威引用:状态修改');
    });
    // Video控制器
    Route::prefix('video')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\VideoController::class, 'store'])->name('视频列表:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\VideoController::class, 'update'])->name('视频列表:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\VideoController::class, 'destroy'])->name('视频列表:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\VideoController::class, 'list'])->name('视频列表:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\VideoController::class, 'changeStatus'])->name('视频列表:状态修改');
    });
    // Link控制器
    Route::prefix('link')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\LinkController::class, 'store'])->name('视频列表:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\LinkController::class, 'update'])->name('视频列表:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\LinkController::class, 'destroy'])->name('视频列表:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\LinkController::class, 'list'])->name('视频列表:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\LinkController::class, 'changeStatus'])->name('视频列表:状态修改');
    });
    // TeamMember控制器
    Route::prefix('team-member')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\TeamMemberController::class, 'store'])->name('团队成员:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\TeamMemberController::class, 'update'])->name('团队成员:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\TeamMemberController::class, 'destroy'])->name('团队成员:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\TeamMemberController::class, 'list'])->name('团队成员:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\TeamMemberController::class, 'changeStatus'])->name('团队成员:状态修改');
    });
    // Office控制器
    Route::prefix('office')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\OfficeController::class, 'store'])->name('办公室列表:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\OfficeController::class, 'update'])->name('办公室列表:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\OfficeController::class, 'destroy'])->name('办公室列表:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\OfficeController::class, 'list'])->name('办公室列表:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\OfficeController::class, 'changeStatus'])->name('办公室列表:状态修改');
    });
    // Qualification控制器
    Route::prefix('qualification')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\QualificationController::class, 'store'])->name('资质认证:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\QualificationController::class, 'update'])->name('资质认证:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\QualificationController::class, 'destroy'])->name('资质认证:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\QualificationController::class, 'list'])->name('资质认证:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\QualificationController::class, 'changeStatus'])->name('资质认证:状态修改');
    });
    // Comment控制器
    Route::prefix('comment')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\CommentController::class, 'store'])->name('资质认证:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\CommentController::class, 'update'])->name('资质认证:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\CommentController::class, 'destroy'])->name('资质认证:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\CommentController::class, 'list'])->name('资质认证:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\CommentController::class, 'changeStatus'])->name('资质认证:状态修改');
    });
    // History控制器
    Route::prefix('history')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\HistoryController::class, 'store'])->name('发展历程:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\HistoryController::class, 'update'])->name('发展历程:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\HistoryController::class, 'destroy'])->name('发展历程:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\HistoryController::class, 'list'])->name('发展历程:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\HistoryController::class, 'changeStatus'])->name('发展历程:状态修改');
    });
    // Partner控制器
    Route::prefix('partner')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PartnerController::class, 'store'])->name('合作伙伴:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PartnerController::class, 'update'])->name('合作伙伴:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\PartnerController::class, 'destroy'])->name('合作伙伴:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\PartnerController::class, 'list'])->name('合作伙伴:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PartnerController::class, 'changeStatus'])->name('合作伙伴:状态修改');
    });
    // Problem控制器
    Route::prefix('problem')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\ProblemController::class, 'store'])->name('常见问题:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\ProblemController::class, 'update'])->name('常见问题:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\ProblemController::class, 'destroy'])->name('常见问题:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\ProblemController::class, 'list'])->name('常见问题:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\ProblemController::class, 'changeStatus'])->name('常见问题:状态修改');
    });
});

Route::get('site/file-management/download/{site}', [Modules\Site\Http\Controllers\FileManagement::class, 'download'])->name('站点端:文件管理:文件下载');
