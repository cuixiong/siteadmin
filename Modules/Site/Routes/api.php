<?php

use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\SiteRuleMiddleware;
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
    SiteRuleMiddleware::class,
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
        Route::get('dir-list-one', [Modules\Site\Http\Controllers\FileManagement::class, 'DirListOne'])->name('文件管理:文件夹列表(单层)');

        Route::post('oss-file-add', [Modules\Site\Http\Controllers\FileManagement::class, 'ossFileAdd'])->name('站点端:文件管理:oss文件上传');
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
        Route::any('get-header-title', [Modules\Site\Http\Controllers\ProductsController::class, 'getHeaderTitle'])->name('报告管理:获取自定义表头');


        Route::post('store', [Modules\Site\Http\Controllers\ProductsController::class, 'store'])->name('报告管理:新增报告');
        Route::get('is-exist', [Modules\Site\Http\Controllers\ProductsController::class, 'isExist'])->name('报告管理:报告是否存在');
        Route::post('check-sensitive-word', [Modules\Site\Http\Controllers\ProductsController::class, 'checkSensitiveWord'])->name('报告管理:检测是否有敏感词');
        Route::get('handler-sensitive-word', [Modules\Site\Http\Controllers\ProductsController::class, 'handlerSenWordsProduct'])->name('报告管理:处理含有敏感词的报告');
        Route::get('get-sensitive-word-cnt', [Modules\Site\Http\Controllers\ProductsController::class, 'getSenWordsProductCnt'])->name('报告管理:返回敏感词报告的个数');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\ProductsController::class, 'form'])->name('报告管理:报告单查');
        Route::post('update', [Modules\Site\Http\Controllers\ProductsController::class, 'update'])->name('报告管理:修改报告');
        Route::post('destroy', [Modules\Site\Http\Controllers\ProductsController::class, 'destroy'])->name('报告管理:删除报告');
        Route::post('discount', [Modules\Site\Http\Controllers\ProductsController::class, 'discount'])->name('报告管理:设置折扣');
        Route::post('export', [Modules\Site\Http\Controllers\ProductsController::class, 'export'])->name('报告管理:批量导出');
        Route::post('newExport', [Modules\Site\Http\Controllers\ProductsController::class, 'newExport'])->name('报告管理:新批量导出');
        Route::post('export-process', [Modules\Site\Http\Controllers\ProductsController::class, 'exportProcess'])->name('报告管理:导出进度');
        Route::post('export-file-download', [Modules\Site\Http\Controllers\ProductsController::class, 'exportFileDownload'])->name('报告管理:下载导出文件');
        Route::post('newexport-file-download', [Modules\Site\Http\Controllers\ProductsController::class, 'newExportFileDownload'])->name('报告管理:新下载导出文件');

        Route::get('batch-update-param', [Modules\Site\Http\Controllers\ProductsController::class, 'batchUpdateParam'])->name('报告管理:批量修改参数');
        Route::get('batch-update-option', [Modules\Site\Http\Controllers\ProductsController::class, 'batchUpdateOption'])->name('报告管理:批量修改参数子项');
        Route::post('batch-update', [Modules\Site\Http\Controllers\ProductsController::class, 'batchUpdate'])->name('报告管理:批量修改');
        Route::post('batch-delete', [Modules\Site\Http\Controllers\ProductsController::class, 'batchDelete'])->name('报告管理:批量删除');
        Route::post('quick-search', [Modules\Site\Http\Controllers\ProductsController::class, 'QuickSearch'])->name('报告管理:快速搜索');
        Route::get('quick-search-dictionary', [Modules\Site\Http\Controllers\ProductsController::class, 'QuickSearchDictionary'])->name('报告管理:快速搜索-字典数据');

    });

    // ProductsUploadLog 控制器
    Route::prefix('products-upload-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ProductsUploadLogController::class, 'list'])->name('上传记录:记录列表');
        Route::get('get-publisher', [Modules\Site\Http\Controllers\ProductsUploadLogController::class, 'getPublisher'])->name('上传记录:出版商数据');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\ProductsUploadLogController::class, 'form'])->name('上传记录:查看单条记录');
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
        Route::post('change-hot', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'changeHot'])->name('报告分类:修改热门状态');
        Route::post('change-recommend', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'changeRecommend'])->name('报告分类:修改推荐状态');
        Route::post('change-showhome', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'changeShowhome'])->name('报告分类:修改首页显示');
        Route::post('change-sort', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'changeSort'])->name('报告分类:排序修改');
        Route::post('set-header-title', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'setHeaderTitle'])->name('报告分类:设置自定义表头');
        Route::get('get-category', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'getCategory'])->name('报告分类:获取某层分类');
        Route::get('get-category-without-self', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'getCategoryWithoutSelf'])->name('报告分类:获取分类(不包含自身)');


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

        Route::get('form/{id}', [Modules\Site\Http\Controllers\OrderController::class, 'form'])->name('订单管理:根据id查询订单');
        Route::post('update', [Modules\Site\Http\Controllers\OrderController::class, 'update'])->name('订单管理:修改订单');
        Route::post('destroy', [Modules\Site\Http\Controllers\OrderController::class, 'destroy'])->name('订单管理:删除订单');

        Route::post('send-order-email', [App\Http\Controllers\SiteEmailController::class, 'sendOrderEmail'])->name('订单管理:补发邮件');

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
        Route::post('change-status', [Modules\Site\Http\Controllers\InvoiceController::class, 'changeStatus'])->name('发票管理:状态修改');
        Route::post('change-apply-status', [Modules\Site\Http\Controllers\InvoiceController::class, 'changeApplyStatus'])->name('发票管理:发票状态修改');

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

    // NewsCategory 控制器
    Route::prefix('news-category')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\NewsCategoryController::class, 'list'])->name('新闻分类:新闻分类列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\NewsCategoryController::class, 'searchDroplist'])->name('新闻分类:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\NewsCategoryController::class, 'changeStatus'])->name('新闻分类:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\NewsCategoryController::class, 'changeSort'])->name('新闻分类:排序修改');

        Route::post('store', [Modules\Site\Http\Controllers\NewsCategoryController::class, 'store'])->name('新闻分类:新增新闻');
        Route::post('update', [Modules\Site\Http\Controllers\NewsCategoryController::class, 'update'])->name('新闻分类:修改新闻');
        Route::post('destroy', [Modules\Site\Http\Controllers\NewsCategoryController::class, 'destroy'])->name('新闻分类:删除新闻');

    });

    // News 控制器
    Route::prefix('news')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\NewsController::class, 'list'])->name('新闻管理:新闻列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\NewsController::class, 'searchDroplist'])->name('新闻管理:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\NewsController::class, 'changeStatus'])->name('新闻管理:状态修改');
        Route::post('change-home', [Modules\Site\Http\Controllers\NewsController::class, 'changeHome'])->name('新闻管理:首页状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\NewsController::class, 'changeSort'])->name('新闻管理:排序修改');

        Route::post('store', [Modules\Site\Http\Controllers\NewsController::class, 'store'])->name('新闻管理:新增新闻');
        Route::post('update', [Modules\Site\Http\Controllers\NewsController::class, 'update'])->name('新闻管理:修改新闻');
        Route::post('destroy', [Modules\Site\Http\Controllers\NewsController::class, 'destroy'])->name('新闻管理:删除新闻');

        Route::get('batch-update-param', [Modules\Site\Http\Controllers\NewsController::class, 'batchUpdateParam'])->name('新闻管理:批量修改参数');
        Route::get('batch-update-option', [Modules\Site\Http\Controllers\NewsController::class, 'batchUpdateOption'])->name('新闻管理:批量修改参数子项');
        Route::post('batch-update', [Modules\Site\Http\Controllers\NewsController::class, 'batchUpdate'])->name('新闻管理:批量修改');

    });

    // 热点资讯 控制器
    Route::prefix('information')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\InformationController::class, 'list'])->name('资讯管理:资讯列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\InformationController::class, 'searchDroplist'])->name('资讯管理:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\InformationController::class, 'changeStatus'])->name('资讯管理:状态修改');
        Route::post('change-home', [Modules\Site\Http\Controllers\InformationController::class, 'changeHome'])->name('资讯管理:首页状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\InformationController::class, 'changeSort'])->name('资讯管理:排序修改');

        Route::post('store', [Modules\Site\Http\Controllers\InformationController::class, 'store'])->name('资讯管理:新增资讯');
        Route::post('update', [Modules\Site\Http\Controllers\InformationController::class, 'update'])->name('资讯管理:修改资讯');
        Route::post('destroy', [Modules\Site\Http\Controllers\InformationController::class, 'destroy'])->name('资讯管理:删除资讯');


        Route::get('batch-update-param', [Modules\Site\Http\Controllers\InformationController::class, 'batchUpdateParam'])->name('资讯管理:批量修改参数');
        Route::get('batch-update-option', [Modules\Site\Http\Controllers\InformationController::class, 'batchUpdateOption'])->name('资讯管理:批量修改参数子项');
        Route::post('batch-update', [Modules\Site\Http\Controllers\InformationController::class, 'batchUpdate'])->name('资讯管理:批量修改');
    });

    // 模版分类 控制器
    Route::prefix('template-category')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\TemplateCategoryController::class, 'list'])->name('模版分类管理:模版分类列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\TemplateCategoryController::class, 'searchDroplist'])->name('模版分类管理:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\TemplateCategoryController::class, 'changeStatus'])->name('模版分类管理:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\TemplateCategoryController::class, 'changeSort'])->name('模版分类管理:排序修改');

        Route::post('store', [Modules\Site\Http\Controllers\TemplateCategoryController::class, 'store'])->name('模版分类管理:新增模版分类');
        Route::post('update', [Modules\Site\Http\Controllers\TemplateCategoryController::class, 'update'])->name('模版分类管理:修改模版分类');
        Route::post('destroy', [Modules\Site\Http\Controllers\TemplateCategoryController::class, 'destroy'])->name('模版分类管理:删除模版分类');

    });

    // 模版 控制器
    Route::prefix('template')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\TemplateController::class, 'list'])->name('模版管理:模版列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\TemplateController::class, 'searchDroplist'])->name('模版管理:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\TemplateController::class, 'changeStatus'])->name('模版管理:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\TemplateController::class, 'changeSort'])->name('模版管理:排序修改');

        Route::post('store', [Modules\Site\Http\Controllers\TemplateController::class, 'store'])->name('模版管理:新增模版');
        Route::post('update', [Modules\Site\Http\Controllers\TemplateController::class, 'update'])->name('模版管理:修改模版');
        Route::post('destroy', [Modules\Site\Http\Controllers\TemplateController::class, 'destroy'])->name('模版管理:删除模版');


        Route::get('copy-word-by-template', [Modules\Site\Http\Controllers\TemplateController::class, 'copyWordByTemplate'])->name('模版管理:根据模板返回文字');
    });

    // 敏感词路由
    Route::prefix('senwords')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\SensitiveWordsController::class, 'list'])->name('敏感词管理:模版列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\SensitiveWordsController::class, 'searchDroplist'])->name('敏感词管理:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\SensitiveWordsController::class, 'changeStatus'])->name('敏感词管理:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\SensitiveWordsController::class, 'changeSort'])->name('敏感词管理:排序修改');
        Route::post('store', [Modules\Site\Http\Controllers\SensitiveWordsController::class, 'store'])->name('敏感词管理:新增模版');
        Route::post('update', [Modules\Site\Http\Controllers\SensitiveWordsController::class, 'update'])->name('敏感词管理:修改模版');
        Route::post('destroy', [Modules\Site\Http\Controllers\SensitiveWordsController::class, 'destroy'])->name('敏感词管理:删除模版');
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
        Route::post('destroy', [Modules\Site\Http\Controllers\EmailLogController::class, 'destroy'])->name('邮箱日志:日志删除');
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

        Route::get('batch-update-param', [Modules\Site\Http\Controllers\MenuController::class, 'batchUpdateParam'])->name('导航菜单:批量修改参数');
        Route::get('batch-update-option', [Modules\Site\Http\Controllers\MenuController::class, 'batchUpdateOption'])->name('导航菜单:批量修改参数子项');
        Route::post('batch-update', [Modules\Site\Http\Controllers\MenuController::class, 'batchUpdate'])->name('导航菜单:批量修改');
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
        Route::any('update', [Modules\Site\Http\Controllers\UserController::class, 'update'])->name('用户列表:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\UserController::class, 'destroy'])->name('用户列表:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\UserController::class, 'list'])->name('用户列表:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\UserController::class, 'changeStatus'])->name('用户列表:状态修改');
        Route::get('options', [Modules\Site\Http\Controllers\UserController::class, 'options'])->name('用户列表:字典数据');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\UserController::class, 'form'])->name('用户列表:用户详情');
    });
    // Pay控制器
    Route::prefix('pay')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PayController::class, 'store'])->name('支付列表:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PayController::class, 'update'])->name('支付列表:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\PayController::class, 'destroy'])->name('支付列表:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\PayController::class, 'list'])->name('支付列表:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PayController::class, 'changeStatus'])->name('支付列表:状态修改');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\PayController::class, 'searchDroplist'])->name('支付列表:搜索下拉列表数据');
    });
    // Applyfor控制器
    Route::prefix('applyfor')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\ApplyforController::class, 'store'])->name('申请样本:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\ApplyforController::class, 'update'])->name('申请样本:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\ApplyforController::class, 'destroy'])->name('申请样本:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\ApplyforController::class, 'list'])->name('申请样本:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\ApplyforController::class, 'changeStatus'])->name('申请样本:状态修改');
    });
    // MessageCategory控制器
    Route::prefix('message-category')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\MessageCategoryController::class, 'store'])->name('留言分类:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\MessageCategoryController::class, 'update'])->name('留言分类:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\MessageCategoryController::class, 'destroy'])->name('留言分类:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\MessageCategoryController::class, 'list'])->name('留言分类:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\MessageCategoryController::class, 'changeStatus'])->name('留言分类:状态修改');
    });
    // Plate控制器
    Route::prefix('plate')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PlateController::class, 'store'])->name('页面板块:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PlateController::class, 'update'])->name('页面板块:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\PlateController::class, 'destroy'])->name('页面板块:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\PlateController::class, 'list'])->name('页面板块:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PlateController::class, 'changeStatus'])->name('页面板块:状态修改');
        Route::get('options', [Modules\Site\Http\Controllers\PlateController::class, 'options'])->name('页面板块:字典数据');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\PlateController::class, 'form'])->name('页面板块:数据单查');
        Route::get('children', [Modules\Site\Http\Controllers\PlateController::class, 'children'])->name('页面板块:查询子级列表');
    });
    // Plate-Value控制器
    Route::prefix('plate-value')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PlateValueController::class, 'store'])->name('页面板块子级:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PlateValueController::class, 'update'])->name('页面板块子级:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\PlateValueController::class, 'destroy'])->name('页面板块子级:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\PlateValueController::class, 'list'])->name('页面板块子级:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PlateValueController::class, 'changeStatus'])->name('页面板块子级:状态修改');
        Route::post('change-hidden', [Modules\Site\Http\Controllers\PlateValueController::class, 'changeStatus'])->name('页面板块子级:状态修改');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\PlateValueController::class, 'form'])->name('页面板块子级:数据单查');

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
        Route::post('change-sort', [Modules\Site\Http\Controllers\AuthorityController::class, 'changeSort'])->name('权威引用:修改排序');
        Route::get('options', [Modules\Site\Http\Controllers\AuthorityController::class, 'options'])->name('权威引用:字典数据');

        Route::get('batch-update-param', [Modules\Site\Http\Controllers\AuthorityController::class, 'batchUpdateParam'])->name('权威引用:批量修改参数');
        Route::get('batch-update-option', [Modules\Site\Http\Controllers\AuthorityController::class, 'batchUpdateOption'])->name('权威引用:批量修改参数子项');
        Route::post('batch-update', [Modules\Site\Http\Controllers\AuthorityController::class, 'batchUpdate'])->name('权威引用:批量修改');
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
        Route::get('options', [Modules\Site\Http\Controllers\TeamMemberController::class, 'options'])->name('团队成员:字典数据');
        Route::post('change-analyst', [Modules\Site\Http\Controllers\TeamMemberController::class, 'ChangeAnalyst'])->name('团队成员:分析师状态修改');
        Route::post('change-show-product', [Modules\Site\Http\Controllers\TeamMemberController::class, 'changeShowProduct'])->name('团队成员:分析师报告显示修改');
    });
    // Office控制器
    Route::prefix('office')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\OfficeController::class, 'store'])->name('办公室列表:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\OfficeController::class, 'update'])->name('办公室列表:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\OfficeController::class, 'destroy'])->name('办公室列表:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\OfficeController::class, 'list'])->name('办公室列表:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\OfficeController::class, 'changeStatus'])->name('办公室列表:状态修改');
        Route::post('change-view-status', [Modules\Site\Http\Controllers\OfficeController::class, 'changeViewStatus'])->name('办公室列表:字段状态修改');
        Route::any('options', [Modules\Site\Http\Controllers\OfficeController::class, 'options'])->name('办公室列表:下拉列表');
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
        Route::post('store', [Modules\Site\Http\Controllers\CommentController::class, 'store'])->name('客户评价:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\CommentController::class, 'update'])->name('客户评价:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\CommentController::class, 'destroy'])->name('客户评价:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\CommentController::class, 'list'])->name('客户评价:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\CommentController::class, 'changeStatus'])->name('客户评价:状态修改');
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

    // LanguageWebsite控制器
    Route::prefix('language-website')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\LanguageWebsiteController::class, 'store'])->name('其它语言网站:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\LanguageWebsiteController::class, 'update'])->name('其它语言网站:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\LanguageWebsiteController::class, 'destroy'])->name('其它语言网站:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\LanguageWebsiteController::class, 'list'])->name('其它语言网站:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\LanguageWebsiteController::class, 'changeStatus'])->name('其它语言网站:状态修改');
    });
    // FaqCategory控制器
    Route::prefix('faq-category')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\FaqCategoryController::class, 'store'])->name('常见问题分类:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\FaqCategoryController::class, 'update'])->name('常见问题分类:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\FaqCategoryController::class, 'destroy'])->name('常见问题分类:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\FaqCategoryController::class, 'list'])->name('常见问题分类:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\FaqCategoryController::class, 'changeStatus'])->name('常见问题分类:状态修改');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\FaqCategoryController::class, 'searchDroplist'])->name('常见问题分类:搜索下拉列表数据');
    });
    // Problem控制器
    Route::prefix('problem')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\ProblemController::class, 'store'])->name('常见问题:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\ProblemController::class, 'update'])->name('常见问题:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\ProblemController::class, 'destroy'])->name('常见问题:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\ProblemController::class, 'list'])->name('常见问题:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\ProblemController::class, 'changeStatus'])->name('常见问题:状态修改');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\ProblemController::class, 'searchDroplist'])->name('常见问题:搜索下拉列表数据');
    });
    // ContactUs控制器
    Route::prefix('contact-us')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\ContactUsController::class, 'store'])->name('联系我们:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\ContactUsController::class, 'update'])->name('联系我们:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\ContactUsController::class, 'destroy'])->name('联系我们:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\ContactUsController::class, 'list'])->name('联系我们:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\ContactUsController::class, 'changeStatus'])->name('联系我们:状态修改');
        Route::get('options', [Modules\Site\Http\Controllers\ContactUsController::class, 'options'])->name('联系我们:字典数据');

        Route::get('batch-update-param', [Modules\Site\Http\Controllers\ContactUsController::class, 'batchUpdateParam'])->name('联系我们:批量修改参数');
        Route::get('batch-update-option', [Modules\Site\Http\Controllers\ContactUsController::class, 'batchUpdateOption'])->name('联系我们:批量修改参数子项');
        Route::post('batch-update', [Modules\Site\Http\Controllers\ContactUsController::class, 'batchUpdate'])->name('联系我们:批量修改');
        Route::get('again-send-email', [Modules\Site\Http\Controllers\ContactUsController::class, 'againSendEmail'])->name('联系我们:重新发送邮件');
    });

    Route::prefix('message-language-version')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\MessageLanguageVersionController::class, 'store'])->name('其它语言网站:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\MessageLanguageVersionController::class, 'update'])->name('其它语言网站:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\MessageLanguageVersionController::class, 'destroy'])->name('其它语言网站:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\MessageLanguageVersionController::class, 'list'])->name('其它语言网站:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\MessageLanguageVersionController::class, 'changeStatus'])->name('其它语言网站:状态修改');
    });

    // Page控制器
    Route::prefix('page')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PageController::class, 'store'])->name('单页管理:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PageController::class, 'update'])->name('单页管理:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\PageController::class, 'destroy'])->name('单页管理:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\PageController::class, 'list'])->name('单页管理:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PageController::class, 'changeStatus'])->name('单页管理:状态修改');
        Route::get('options', [Modules\Site\Http\Controllers\PageController::class, 'options'])->name('单页管理:字典数据');
    });

    // QuoteCategory 控制器
    Route::prefix('quote-category')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\QuoteCategoryController::class, 'list'])->name('权威引用分类:数据列表');
        Route::post('store', [Modules\Site\Http\Controllers\QuoteCategoryController::class, 'store'])->name('权威引用分类:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\QuoteCategoryController::class, 'update'])->name('权威引用分类:数据编辑');
        Route::post('destroy', [Modules\Site\Http\Controllers\QuoteCategoryController::class, 'destroy'])->name('权威引用分类:删除操作');
        Route::post('change-status', [Modules\Site\Http\Controllers\QuoteCategoryController::class, 'changeStatus'])->name('权威引用分类:修改状态');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\QuoteCategoryController::class, 'searchDroplist'])->name('权威引用分类:搜索下拉列表数据');
    });
    Route::get('xun-add', [Modules\Site\Http\Controllers\XunSearch::class, 'add'])->name('测试接口:迅速新增');
    Route::get('xun-search', [Modules\Site\Http\Controllers\XunSearch::class, 'search'])->name('测试接口:迅速搜索');
    Route::get('xun-clean', [Modules\Site\Http\Controllers\XunSearch::class, 'clean'])->name('测试接口:迅速清空');
    Route::get('xun-test', [Modules\Site\Http\Controllers\XunSearch::class, 'AddToMQ'])->name('测试接口:迅速清空');
    Route::get('xun-testtokenizer', [Modules\Site\Http\Controllers\XunSearch::class, 'testTokenizer'])->name('测试接口');

    Route::get('test', [Modules\Site\Http\Controllers\TestController::class, 'test'])->name('站点测试:测试接口');
    Route::get('test2', [Modules\Site\Http\Controllers\TestController::class, 'test2'])->name('站点测试:测试接口');
    Route::get('test3', [Modules\Site\Http\Controllers\TestController::class, 'test3'])->name('站点测试:测试接口');
    Route::get('test-search', [Modules\Site\Http\Controllers\TestController::class, 'searchTest'])->name('站点测试:测试查询');

    // sync报告
    Route::prefix('sync-third-product')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\SyncThirdProductController::class, 'list'])->name('同步报告:同步日志列表');
        Route::post('destroy', [Modules\Site\Http\Controllers\SyncThirdProductController::class, 'destroy'])->name('同步报告:删除操作');
        Route::post('form/{id}', [Modules\Site\Http\Controllers\SyncThirdProductController::class, 'form'])->name('同步报告:查看详情');
        Route::get('sync', [Modules\Site\Http\Controllers\SyncThirdProductController::class, 'sync'])->name('同步报告:同步数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\SyncThirdProductController::class, 'searchDroplist'])->name('同步报告:搜索下拉列表数据');
        Route::post('change-auto-sync-status', [Modules\Site\Http\Controllers\SyncThirdProductController::class, 'changeAutoSyncStatus'])->name('同步报告:搜索下拉列表数据');
    });

    // sync字段
    Route::prefix('sync-third-field')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\SyncFieldController::class, 'list'])->name('同步字段:列表');
        Route::post('store', [Modules\Site\Http\Controllers\SyncFieldController::class, 'store'])->name('同步字段:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\SyncFieldController::class, 'update'])->name('同步字段:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\SyncFieldController::class, 'destroy'])->name('同步字段:数据删除');
        Route::post('form/{id}', [Modules\Site\Http\Controllers\SyncFieldController::class, 'form'])->name('同步字段:详情');

        Route::get('search-droplist', [Modules\Site\Http\Controllers\SyncFieldController::class, 'searchDroplist'])->name('同步出版商:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\SyncFieldController::class, 'changeStatus'])->name('同步出版商:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\SyncFieldController::class, 'changeSort'])->name('同步出版商:排序修改');
    });

    // sync出版商
    Route::prefix('sync-third-publisher')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\SyncPublisherController::class, 'list'])->name('同步出版商:列表');
        Route::post('store', [Modules\Site\Http\Controllers\SyncPublisherController::class, 'store'])->name('同步出版商:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\SyncPublisherController::class, 'update'])->name('同步出版商:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\SyncPublisherController::class, 'destroy'])->name('同步出版商:数据删除');
        Route::post('form/{id}', [Modules\Site\Http\Controllers\SyncPublisherController::class, 'form'])->name('同步出版商:详情');

        Route::get('search-droplist', [Modules\Site\Http\Controllers\SyncPublisherController::class, 'searchDroplist'])->name('同步出版商:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\SyncPublisherController::class, 'changeStatus'])->name('同步出版商:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\SyncPublisherController::class, 'changeSort'])->name('同步出版商:排序修改');
    });


    // 报告浏览记录
    Route::prefix('view-products-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ViewProductsLogController::class, 'list'])->name('报告浏览记录:数据列表');
        Route::post('destroy', [Modules\Site\Http\Controllers\ViewProductsLogController::class, 'destroy'])->name('报告浏览记录:数据删除');
        Route::post('change-status', [Modules\Site\Http\Controllers\ViewProductsLogController::class, 'changeStatus'])->name('报告浏览记录:状态修改');
        Route::post('form/{id}', [Modules\Site\Http\Controllers\ViewProductsLogController::class, 'form'])->name('报告浏览记录:查询详情');
        Route::post('export', [Modules\Site\Http\Controllers\ViewProductsLogController::class, 'export'])->name('报告管理:批量导出');
    });

    // ProductsExportLog 控制器
    Route::prefix('view-products-export-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ViewProductsExportLogController::class, 'list'])->name('导出记录:导出列表');
        Route::post('destroy', [Modules\Site\Http\Controllers\ViewProductsExportLogController::class, 'destroy'])->name('导出记录:删除记录');
        Route::post('export-process', [Modules\Site\Http\Controllers\ViewProductsExportLogController::class, 'exportProcess'])->name('导出记录:导出进度');
        Route::post('export-file-download', [Modules\Site\Http\Controllers\ViewProductsExportLogController::class, 'exportFileDownload'])->name('导出记录:下载导出文件');
    });

    // 封禁IP记录
    Route::prefix('ip-ban-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\IpBanLogController::class, 'list'])->name('封禁IP日志:数据列表');
        Route::post('destroy', [Modules\Site\Http\Controllers\IpBanLogController::class, 'destroy'])->name('封禁IP日志:数据删除');
        Route::post('change-status', [Modules\Site\Http\Controllers\IpBanLogController::class, 'changeStatus'])->name('封禁IP日志:状态修改');
        Route::post('form/{id}', [Modules\Site\Http\Controllers\IpBanLogController::class, 'form'])->name('封禁IP日志:查询详情');
        Route::post('ip-unban', [Modules\Site\Http\Controllers\IpBanLogController::class, 'IpUnban'])->name('IP解封');
        Route::post('add-white', [Modules\Site\Http\Controllers\IpBanLogController::class, 'addWhiteList'])->name('添加白名单');
        Route::get('copy-field', [Modules\Site\Http\Controllers\IpBanLogController::class, 'copyField'])->name('访问日志:拷贝字段');
    });

    // 封禁IP记录
    Route::prefix('ua-ban-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\RequestLogController::class, 'list'])->name('封禁UA日志:数据列表');
        Route::post('destroy', [Modules\Site\Http\Controllers\RequestLogController::class, 'destroy'])->name('封禁UA日志:数据删除');
        Route::post('change-status', [Modules\Site\Http\Controllers\RequestLogController::class, 'changeStatus'])->name('封禁UA日志:状态修改');
        Route::post('form/{id}', [Modules\Site\Http\Controllers\RequestLogController::class, 'form'])->name('封禁UA日志:查询详情');
        Route::post('ua-unban', [Modules\Site\Http\Controllers\RequestLogController::class, 'UaUnban'])->name('UA解封');
        Route::post('add-white', [Modules\Site\Http\Controllers\RequestLogController::class, 'addWhiteList'])->name('添加白名单');
        Route::get('copy-field', [Modules\Site\Http\Controllers\RequestLogController::class, 'copyField'])->name('访问日志:拷贝字段');
    });


    // 封禁白名单
    Route::prefix('ban-white-list')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\BanWhiteListController::class, 'list'])->name('封禁白名单:数据列表');
        Route::post('destroy', [Modules\Site\Http\Controllers\BanWhiteListController::class, 'destroy'])->name('封禁白名单:数据删除');
        Route::post('change-status', [Modules\Site\Http\Controllers\BanWhiteListController::class, 'changeStatus'])->name('封禁白名单:状态修改');
        Route::post('form/{id}', [Modules\Site\Http\Controllers\BanWhiteListController::class, 'form'])->name('封禁白名单:查询详情');
        Route::post('update', [Modules\Site\Http\Controllers\BanWhiteListController::class, 'update'])->name('封禁白名单:修改');
        Route::post('store', [Modules\Site\Http\Controllers\BanWhiteListController::class, 'store'])->name('封禁白名单:新增');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\BanWhiteListController::class, 'searchDroplist'])->name('同步出版商:搜索下拉列表数据');
    });


    // 访问日志记录
    Route::prefix('access-log')->group(function () {
        Route::get('search-droplist', [Modules\Site\Http\Controllers\AccessLogController::class, 'searchDroplist'])->name('访问日志:搜索下拉列表数据');
        Route::get('report-forms', [Modules\Site\Http\Controllers\AccessLogController::class, 'ReportForms'])->name('访问日志:报表');
        Route::get('copy-field', [Modules\Site\Http\Controllers\AccessLogController::class, 'copyField'])->name('访问日志:拷贝字段');
        Route::get('copy-ua-field', [Modules\Site\Http\Controllers\AccessLogController::class, 'copyUaField'])->name('访问日志:拷贝字段');
        Route::get('access-detail-list', [Modules\Site\Http\Controllers\AccessLogController::class, 'accessDetailList'])->name('访问日志:报表详情');
        Route::post('access-log-del', [Modules\Site\Http\Controllers\AccessLogController::class, 'accessLogDel'])->name('访问日志:报表日志删除');

        Route::post('access-log-export', [Modules\Site\Http\Controllers\AccessLogController::class, 'accessLogExport'])->name('访问日志:报表日志导出');
        Route::post('export-process', [Modules\Site\Http\Controllers\AccessLogController::class, 'exportProcess'])->name('访问日志:导出进度');
        Route::post('newexport-file-download', [Modules\Site\Http\Controllers\AccessLogController::class, 'newExportFileDownload'])->name('访问日志:新下载导出文件');

    });


    //定时任务脚本(前端按钮接口调用)
    Route::prefix('crontab')->group(function () {
        Route::get('set-product-status', [Modules\Site\Http\Controllers\SiteCrontabController::class, 'handlerProductStatus'])->name('定时任务:设置产品状态');
    });


    Route::prefix('public')->group(function () {
        Route::get('get-read-msg-cnt', [Modules\Site\Http\Controllers\PublicController::class, 'getNoReadMsgCnt'])->name('公共:获取未读消息数量');
        Route::get('get-init-data', [Modules\Site\Http\Controllers\PublicController::class, 'getInitData'])->name('公共:获取初始化数据');
        Route::any('truncate-table', [Modules\Site\Http\Controllers\PublicController::class, 'truncateTable'])->name('清空数据表');
        Route::get('get-site-security-conf', [Modules\Site\Http\Controllers\PublicController::class, 'getSiteSecurityConf'])->name('获取站点安全数据');
        Route::get('test', [Modules\Site\Http\Controllers\PublicController::class, 'test'])->name('测试');
    });

    // OperationLogController 控制器
    Route::prefix('operation-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\OperationLogController::class, 'list'])->name('操作日志:数据列表');
        Route::post('destroy', [Modules\Site\Http\Controllers\OperationLogController::class, 'destroy'])->name('操作日志:删除操作');
        Route::get('options', [Modules\Site\Http\Controllers\OperationLogController::class, 'options'])->name('操作日志:字典数据');
    });


    // PriceEdition 控制器
    Route::prefix('price-edition')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\PriceEditionController::class, 'list'])->name('价格版本管理:价格版本列表');
        Route::get('option', [Modules\Site\Http\Controllers\PriceEditionController::class, 'option'])->name('价格版本管理:价格版本列表下拉列表数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\PriceEditionController::class, 'searchDroplist'])->name('价格版本管理:搜索下拉列表数据');
        Route::get('sync-admin-price', [Modules\Site\Http\Controllers\PriceEditionController::class, 'syncAdminPrice'])->name('价格版本管理:搜索下拉列表数据');
    });


    // nginxBanList 控制器
    Route::prefix('nginx-ban')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\NginxBanListController::class, 'list'])->name('nginx封禁:封禁列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\NginxBanListController::class, 'searchDroplist'])->name('nginx封禁:搜索下拉列表数据');
        Route::get('unban', [Modules\Site\Http\Controllers\NginxBanListController::class, 'unBan'])->name('nginx封禁:解封');
        Route::get('black-list', [Modules\Site\Http\Controllers\NginxBanListController::class, 'blackList'])->name('nginx封禁:黑名单列表');
        Route::post('del-black', [Modules\Site\Http\Controllers\NginxBanListController::class, 'delBlack'])->name('nginx封禁:删除黑名单');
    });

    // 出版商
    Route::prefix('publisher')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\PublisherController::class, 'list'])->name('出版商:列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\PublisherController::class, 'searchDroplist'])->name('出版商:搜索下拉列表数据');
        Route::get('sync', [Modules\Site\Http\Controllers\PublisherController::class, 'sync'])->name('出版商:同步数据');
        Route::post('change-status',[Modules\Site\Http\Controllers\PublisherController::class, 'changeStatus'])->name('出版商管理:状态修改');
        Route::post('store', [Modules\Site\Http\Controllers\PublisherController::class, 'store'])->name('出版商管理:出版商新增');
        Route::post('update', [Modules\Site\Http\Controllers\PublisherController::class, 'update'])->name('出版商管理:出版商编辑');
        Route::post('destroy', [Modules\Site\Http\Controllers\PublisherController::class, 'destroy'])->name('出版商管理:出版商删除');
    });

    // PostPlatform 发帖平台
    Route::prefix('post-platform')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PostPlatformController::class, 'store'])->name('发帖平台:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PostPlatformController::class, 'update'])->name('发帖平台:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\PostPlatformController::class, 'destroy'])->name('发帖平台:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\PostPlatformController::class, 'list'])->name('发帖平台:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PostPlatformController::class, 'changeStatus'])->name('发帖平台:状态修改');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\PostPlatformController::class, 'searchDroplist'])->name('发帖平台:搜索下拉列表数据');
    });

    // PostSubject 发帖课题
    Route::prefix('post-subject')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\PostSubjectController::class, 'store'])->name('发帖课题:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\PostSubjectController::class, 'update'])->name('发帖课题:数据更新');
        Route::post('destory', [Modules\Site\Http\Controllers\PostSubjectController::class, 'destory'])->name('发帖课题:数据删除');
        Route::get('get-product-info', [Modules\Site\Http\Controllers\PostSubjectController::class, 'getProductInfo'])->name('发帖课题:查询报告');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\PostSubjectController::class, 'form'])->name('发帖课题:报告单查');
        Route::get('list', [Modules\Site\Http\Controllers\PostSubjectController::class, 'list'])->name('发帖课题:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\PostSubjectController::class, 'changeStatus'])->name('发帖课题:状态修改');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\PostSubjectController::class, 'searchDroplist'])->name('发帖课题:搜索下拉列表数据');
        Route::get('advanced-filters', [Modules\Site\Http\Controllers\PostSubjectController::class, 'advancedFilters'])->name('发帖课题:高级筛选数据');
        Route::post('accept', [Modules\Site\Http\Controllers\PostSubjectController::class, 'accept'])->name('发帖课题:领取/分配');

        Route::post('export-subject', [Modules\Site\Http\Controllers\PostSubjectController::class, 'exportSubject'])->name('发帖课题:导出课题');
        Route::post('export-subject-link', [Modules\Site\Http\Controllers\PostSubjectController::class, 'exportSubjectLink'])->name('发帖课题:导出日志');
        Route::post('upload-subject-link', [Modules\Site\Http\Controllers\PostSubjectController::class, 'uploadSubjectLink'])->name('发帖课题:上传日志');
        Route::post('upload-subject-link-old', [Modules\Site\Http\Controllers\PostSubjectController::class, 'uploadSubjectLinkOld'])->name('发帖课题:上传旧日志');
        Route::post('upload-subject-link-old2', [Modules\Site\Http\Controllers\PostSubjectController::class, 'uploadSubjectLinkOld2'])->name('发帖课题:上传旧日志gircn2');

        Route::get('post-subject-log', [Modules\Site\Http\Controllers\PostSubjectController::class, 'postSubjectLog'])->name('发帖课题:操作日志');

        Route::post('set-export-blank-row', [Modules\Site\Http\Controllers\PostSubjectController::class, 'setExportBlankRow'])->name('发帖课题:导出课题设置');
    });


    // PostSubjectLog 发帖课题操作记录
    Route::prefix('post-subject-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\PostSubjectLogController::class, 'list'])->name('发帖操作记录:数据列表');
        Route::post('destory', [Modules\Site\Http\Controllers\PostSubjectLogController::class, 'destory'])->name('发帖操作记录:数据删除');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\PostSubjectLogController::class, 'searchDroplist'])->name('发帖操作记录:搜索下拉列表数据');
    });

    // CaseShare 案例分享
    Route::prefix('case-share')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\CaseShareController::class, 'list'])->name('案例分享:数据列表');
        Route::post('store', [Modules\Site\Http\Controllers\CaseShareController::class, 'store'])->name('案例分享:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\CaseShareController::class, 'update'])->name('案例分享:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\CaseShareController::class, 'destroy'])->name('案例分享:数据删除');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\CaseShareController::class, 'searchDroplist'])->name('案例分享:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\CaseShareController::class, 'changeStatus'])->name('案例分享:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\CaseShareController::class, 'changeSort'])->name('案例分享:排序修改');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\CaseShareController::class, 'form'])->name('案例分享:数据单查');
    });

    // Questions 常见问题
    Route::prefix('questions')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\QuestionsController::class, 'list'])->name('常见问题:数据列表');
        Route::post('store', [Modules\Site\Http\Controllers\QuestionsController::class, 'store'])->name('常见问题:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\QuestionsController::class, 'update'])->name('常见问题:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\QuestionsController::class, 'destroy'])->name('常见问题:数据删除');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\QuestionsController::class, 'searchDroplist'])->name('常见问题:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\QuestionsController::class, 'changeStatus'])->name('常见问题:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\QuestionsController::class, 'changeSort'])->name('常见问题:排序修改');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\QuestionsController::class, 'form'])->name('常见问题:数据单查');
    });

    // Answers 答案模块
    Route::prefix('answers')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\AnswersController::class, 'list'])->name('答案模块:数据列表');
        Route::post('store', [Modules\Site\Http\Controllers\AnswersController::class, 'store'])->name('答案模块:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\AnswersController::class, 'update'])->name('答案模块:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\AnswersController::class, 'destroy'])->name('答案模块:数据删除');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\AnswersController::class, 'searchDroplist'])->name('答案模块:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\AnswersController::class, 'changeStatus'])->name('答案模块:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\AnswersController::class, 'changeSort'])->name('答案模块:排序修改');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\AnswersController::class, 'form'])->name('答案模块:数据单查');
    });

    //自动发帖
    Route::prefix('auto-post')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\AutoPostConfigController::class, 'list'])->name('自动发帖:数据列表');
        Route::post('store', [Modules\Site\Http\Controllers\AutoPostConfigController::class, 'store'])->name('自动发帖:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\AutoPostConfigController::class, 'update'])->name('自动发帖:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\AutoPostConfigController::class, 'destroy'])->name('自动发帖:数据删除');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\AutoPostConfigController::class, 'searchDroplist'])->name('自动发帖:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\AutoPostConfigController::class, 'changeStatus'])->name('自动发帖:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\AutoPostConfigController::class, 'changeSort'])->name('自动发帖:排序修改');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\AutoPostConfigController::class, 'form'])->name('自动发帖:数据单查');
    });

    //发帖日志
    Route::prefix('auto-post-log')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\AutoPostLogController::class, 'list'])->name('自动发帖日志:数据列表');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\AutoPostLogController::class, 'searchDroplist'])->name('自动发帖日志:搜索下拉列表数据');
        Route::post('destroy', [Modules\Site\Http\Controllers\AutoPostLogController::class, 'destroy'])->name('自动发帖日志:数据删除');
    });


    //站点地图
    Route::prefix('site-map-conf')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\SiteMapConfController::class, 'list'])->name('站点地图:数据列表');
        Route::post('store', [Modules\Site\Http\Controllers\SiteMapConfController::class, 'store'])->name('站点地图:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\SiteMapConfController::class, 'update'])->name('站点地图:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\SiteMapConfController::class, 'destroy'])->name('站点地图:数据删除');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\SiteMapConfController::class, 'searchDroplist'])->name('站点地图:搜索下拉列表数据');
        Route::post('change-status', [Modules\Site\Http\Controllers\SiteMapConfController::class, 'changeStatus'])->name('站点地图:状态修改');
        Route::post('change-sort', [Modules\Site\Http\Controllers\SiteMapConfController::class, 'changeSort'])->name('站点地图:排序修改');
        Route::get('form/{id}', [Modules\Site\Http\Controllers\SiteMapConfController::class, 'form'])->name('站点地图:数据单查');
    });
    
    // 货币配置
    Route::prefix('CurrencyConfig')->group(function () {
        Route::post('store', [Modules\Site\Http\Controllers\CurrencyConfigController::class, 'store'])->name('货币配置:数据新增');
        Route::post('update', [Modules\Site\Http\Controllers\CurrencyConfigController::class, 'update'])->name('货币配置:数据更新');
        Route::post('destroy', [Modules\Site\Http\Controllers\CurrencyConfigController::class, 'destroy'])->name('货币配置:数据删除');
        Route::get('list', [Modules\Site\Http\Controllers\CurrencyConfigController::class, 'list'])->name('货币配置:数据列表');
        Route::post('change-status', [Modules\Site\Http\Controllers\CurrencyConfigController::class, 'changeStatus'])->name('货币配置:状态修改');
        Route::post('change-first', [Modules\Site\Http\Controllers\CurrencyConfigController::class, 'changeFirst'])->name('货币配置:状态修改');
        Route::post('change-show-home', [Modules\Site\Http\Controllers\CurrencyConfigController::class, 'changeShowHome'])->name('货币配置:状态修改');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\CurrencyConfigController::class, 'searchDroplist'])->name('货币配置:搜索下拉列表数据');
    });

    Route::get('/test23', [Modules\Site\Http\Controllers\TestController::class, 'test23'])->name('站点端:测试');
});



Route::get('site/file-management/download/{site}', [Modules\Site\Http\Controllers\FileManagement::class, 'download'])->name('站点端:文件管理:文件下载');
Route::get('site/file-management/newdownload/{site}', [Modules\Site\Http\Controllers\FileManagement::class, 'newDownload'])->name('站点端:文件管理:文件下载');
