<?php
use Illuminate\Support\Facades\Route;

// Site控制器
Route::post('admin/site/store', 'SiteController@store')->name('站点新增');
Route::post('admin/site/update', 'SiteController@update')->name('站点更新');
Route::post('admin/site/destroy', 'SiteController@destroy')->name('站点删除');
Route::post('admin/site/move-up-site', 'SiteController@moveUpSite')->name('站点升级');

// Region控制器
Route::post('admin/region/store', 'RegionController@store')->name('地区新增');
Route::post('admin/region/destroy', 'RegionController@destroy')->name('地区删除');
Route::post('admin/region/update', 'RegionController@update')->name('地区编辑');


// Language控制器
Route::post('admin/language/store', 'LanguageController@store')->name('语言新增');
Route::post('admin/language/destroy', 'LanguageController@destroy')->name('语言删除');
Route::post('admin/language/update', 'LanguageController@update')->name('语言编辑');
Route::post('admin/language/changeStatus', 'LanguageController@changeStatus')->name('语言状态切换');

// Publisher控制器
Route::post('admin/publisher/store', 'PublisherController@store')->name('出版商新增');
Route::post('admin/publisher/destroy', 'PublisherController@destroy')->name('出版商删除');
Route::post('admin/publisher/update', 'PublisherController@update')->name('出版商修改');
Route::post('admin/publisher/changeStatus', 'PublisherController@changeStatus')->name('出版商状态切换');
