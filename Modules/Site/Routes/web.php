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
Route::prefix('site')->middleware([
    'web',
    InitializeTenancyByRequestData::class,
])->group(function () {
    Route::get('site/index','SiteController@index');
    Route::get('site/user','SiteController@user');
});
