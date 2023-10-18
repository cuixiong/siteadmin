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
InitializeTenancyByRequestData::$header = 'X-Site';
InitializeTenancyByRequestData::$queryParameter = null;
Route::middleware([
    InitializeTenancyByRequestData::class,
    JwtMiddleware::class,
])->group(function(){
    Route::get('site/select',[\Modules\Site\Http\Controllers\SiteController::class,'select']);
    Route::get('site/update',[\Modules\Site\Http\Controllers\SiteController::class,'update']);
    Route::get('site/insert',[\Modules\Site\Http\Controllers\SiteController::class,'insert']);
    Route::get('site/delete',[\Modules\Site\Http\Controllers\SiteController::class,'delete']);
});