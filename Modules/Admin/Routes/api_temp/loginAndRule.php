<?php
use Illuminate\Support\Facades\Route;

// Site控制器
Route::post('admin/site/store', 'SiteController@store')->name('站点新增');
Route::post('admin/site/update', 'SiteController@update')->name('站点更新');
Route::post('admin/site/destroy', 'SiteController@destroy')->name('站点删除');
Route::post('admin/site/create-site-to-remote-server', 'SiteController@createSiteToRemoteServer')->name('初始化站点');
Route::post('admin/site/update-site-to-remote-server', 'SiteController@updateSiteToRemoteServer')->name('站点升级');
// Route::post('admin/site/move-up-site', 'SiteController@moveUpSite')->name('站点升级');
Route::post('admin/site/init-database', 'SiteController@initDatabase')->name('初始化数据库');
Route::post('admin/site/commit-history', 'SiteController@CommitHistory')->name('git提交记录历史');
Route::post('admin/site/available-upgrade', 'SiteController@availableUpgrade')->name('git可用更新');
Route::post('admin/site/rollback-code', 'SiteController@rollbackCode')->name('版本回退');


Route::post('admin/site/changeStatus', 'SiteController@changeStatus')->name('站点状态切换');

//SiteUpdateLog控制器 
Route::post('admin/site-update-log/destroy', 'SiteUpdateLogController@destroy')->name('升级日志删除');

// Region控制器
Route::post('admin/region/store', 'RegionController@store')->name('地区新增');
Route::post('admin/region/destroy', 'RegionController@destroy')->name('地区删除');
Route::post('admin/region/update', 'RegionController@update')->name('地区编辑');
Route::post('admin/region/changeStatus', 'RegionController@changeStatus')->name('地区状态切换');


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
Route::post('admin/publisher/upload-logo','PublisherController@uploadLogo')->name('上传出版商logo');


// PriceEdition控制器
Route::post('admin/price-edition/store', 'PriceEditionController@store')->name('价格版本新增');
Route::post('admin/price-edition/update', 'PriceEditionController@update')->name('价格版本修改');
Route::post('admin/price-edition/destroy', 'PriceEditionController@destroy')->name('价格版本删除');
Route::post('admin/price-edition/changeStatus', 'PriceEditionController@changeStatus')->name('价格版本状态切换');
