<?php

use Illuminate\Support\Facades\Route;
// Site控制器
Route::post('admin/site/message', 'SiteController@message')->name('站点测试');
Route::post('admin/site/callback-results', 'SiteController@callbackResults')->name('站点更新结果');
Route::get('admin/site/get-catch-git-status', 'SiteController@getCatchGitStatus')->name('返回更新结果');
Route::get('admin/site/list', 'SiteController@list')->name('站点列表');
// Route::get('admin/site/option','SiteController@option')->name('站点option');
Route::get('admin/site/search-droplist','SiteController@searchDroplist')->name('站点搜索下拉列表数据');

//SiteUpdateLog控制器 
Route::get('admin/site-update-log/list', 'SiteUpdateLogController@list')->name('升级站点日志列表');

// Region控制器
Route::get('admin/region/list', 'RegionController@list')->name('地区列表');
Route::get('admin/region/option','RegionController@option')->name('地区option');
Route::get('admin/region/search-droplist','RegionController@searchDroplist')->name('地区搜索下拉列表数据');

// Language控制器
Route::get('admin/language/list', 'LanguageController@list')->name('语言列表');
Route::get('admin/language/option','LanguageController@option')->name('语言option');
Route::get('admin/language/search-droplist','LanguageController@searchDroplist')->name('语言搜索下拉列表数据');

// Publisher控制器
Route::get('admin/publisher/list', 'PublisherController@list')->name('出版商列表');
Route::get('admin/publisher/option','PublisherController@option')->name('出版商option');
Route::get('admin/publisher/search-droplist','PublisherController@searchDroplist')->name('出版商搜索下拉列表数据');



// PriceEdition控制器
Route::get('admin/price-edition/list', 'PriceEditionController@list')->name('价格版本列表');
Route::get('admin/price-edition/option','PriceEditionController@option')->name('价格版本option');
Route::get('admin/price-edition/search-droplist','PriceEditionController@searchDroplist')->name('价格版本搜索下拉列表数据');
