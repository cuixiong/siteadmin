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
])->prefix('site')->group(function(){
    Route::get('site/select',[\Modules\Site\Http\Controllers\SiteController::class,'select']);
    Route::get('site/update',[\Modules\Site\Http\Controllers\SiteController::class,'update']);
    Route::get('site/insert',[\Modules\Site\Http\Controllers\SiteController::class,'insert']);
    Route::get('site/delete',[\Modules\Site\Http\Controllers\SiteController::class,'delete']);

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
    });

    
    // Products 控制器
    Route::prefix('products')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ProductsController::class, 'list'])->name('报告管理:报告列表');
        Route::get('option', [Modules\Site\Http\Controllers\ProductsController::class, 'option'])->name('报告管理:下拉列表数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\ProductsController::class, 'searchDroplist'])->name('报告管理:搜索下拉列表数据');
        Route::post('change-status',[Modules\Site\Http\Controllers\ProductsController::class, 'changeStatus'])->name('报告管理:状态修改');
        Route::post('change-sort',[Modules\Site\Http\Controllers\ProductsController::class, 'changeSort'])->name('报告管理:排序修改');
        Route::post('set-header-title', [Modules\Admin\Http\Controllers\ProductsController::class, 'setHeaderTitle'])->name('报告管理:设置自定义表头');
        
        
        Route::post('store', [Modules\Site\Http\Controllers\ProductsController::class, 'store'])->name('报告管理:新增报告');
        Route::post('update', [Modules\Site\Http\Controllers\ProductsController::class, 'update'])->name('报告管理:修改报告');
        Route::post('destroy', [Modules\Site\Http\Controllers\ProductsController::class, 'destroy'])->name('报告管理:删除报告');
        Route::post('discount', [Modules\Site\Http\Controllers\ProductsController::class, 'discount'])->name('报告管理:设置折扣');
        Route::post('upload-products', [Modules\Site\Http\Controllers\ProductsController::class, 'uploadProducts'])->name('报告管理:上传报告');
        
    });

    // ProductsCategory 控制器
    Route::prefix('products-category')->group(function () {
        Route::get('list', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'list'])->name('报告分类:分类列表');
        Route::get('option', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'option'])->name('报告分类:下拉列表数据');
        Route::get('search-droplist', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'searchDroplist'])->name('报告分类:搜索下拉列表数据');
        Route::post('change-status',[Modules\Site\Http\Controllers\ProductsCategoryController::class, 'changeStatus'])->name('报告分类:状态修改');
        Route::post('change-sort',[Modules\Site\Http\Controllers\ProductsCategoryController::class, 'changeSort'])->name('报告分类:排序修改');
        Route::post('set-header-title', [Modules\Admin\Http\Controllers\ProductsCategoryController::class, 'setHeaderTitle'])->name('报告分类:设置自定义表头');
        

        Route::post('store', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'store'])->name('报告分类:新增分类');
        Route::post('update', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'update'])->name('报告分类:修改分类');
        Route::post('destroy', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'destroy'])->name('报告分类:删除分类');
        Route::post('discount', [Modules\Site\Http\Controllers\ProductsCategoryController::class, 'discount'])->name('报告分类:设置折扣');
        
    });
});
Route::get('site/file-management/download/{site}', [Modules\Site\Http\Controllers\FileManagement::class, 'download'])->name('站点端:文件管理:文件下载');
