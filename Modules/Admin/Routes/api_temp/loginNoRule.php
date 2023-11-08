<?php

use Illuminate\Support\Facades\Route;
// Site控制器
Route::post('admin/site/message', 'SiteController@message')->name('站点测试');
Route::post('admin/site/callback-results', 'SiteController@callbackResults')->name('站点更新结果');
Route::get('admin/site/get-catch-git-status', 'SiteController@getCatchGitStatus')->name('返回更新结果');
Route::get('admin/site/list', 'SiteController@list')->name('站点列表');

// Region控制器
Route::get('admin/region/list', 'RegionController@list')->name('地区列表');
Route::get('admin/region/option','RegionController@option')->name('地区option');

// Language控制器
Route::get('admin/language/list', 'LanguageController@list')->name('语言列表');
Route::get('admin/language/option','LanguageController@option')->name('语言option');

// Publisher控制器
Route::get('admin/publisher/list', 'PublisherController@list')->name('出版商列表');
Route::get('admin/publisher/option','PublisherController@option')->name('出版商option');

// PriceEdition控制器
Route::get('admin/price-edition/list', 'PriceEditionController@list')->name('价格版本列表');
Route::get('admin/price-edition/option','PriceEditionController@option')->name('价格版本option');
