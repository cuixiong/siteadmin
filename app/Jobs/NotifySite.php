<?php
/**
 * NotifySite.php UTF-8
 * 通知站点通知
 *
 * @date    : 2024/9/14 15:45 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Jobs;

use App\Const\NotityTypeConst;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\Country as AdminCountry;
use Modules\Admin\Http\Models\Database;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\SyncSiteLog;
use Modules\Site\Http\Models\Publisher as SitePublisher;
use Modules\Site\Http\Models\BanWhiteList;
use Modules\Site\Http\Models\Country;
use Modules\Admin\Http\Models\Language as AdminLanguage;
use Modules\Admin\Http\Models\PriceEdition as AdminPriceEdition;
use Modules\Admin\Http\Models\PriceEditionValue as AdminPriceEditionValue;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\Language;
use Modules\Site\Http\Models\PriceEdition;
use Modules\Site\Http\Models\PriceEditionValue;
use Modules\Admin\Http\Models\BanWhiteList as AdminBanWhiteList;
use Modules\Admin\Http\Models\System as AdminSystem;
use Modules\Admin\Http\Models\SystemValue as AdminSystemValue;
use Modules\Site\Http\Models\System;
use Modules\Site\Http\Models\SystemValue;

class NotifySite implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, BaseJob;

    public $data           = '';
    public $sourceDbConfig = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data) {
        $this->data = $data;
        $this->sourceDbConfig = Config::get('database.connections.mysql');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        echo "开始".PHP_EOL;
        try {
            $data = json_decode($this->data, true);
            $this->useLocalDb();
            $siteInfo = Site::query()->where('id', $data['siteId'])->first();
            if (empty($siteInfo) || $siteInfo->status != 1) {
                \Log::error('返回结果数据:同步错误  文件路径:'.__CLASS__.'  行号:'.__LINE__);

                return true;
            }
            $sync_type = $data['sync_type'];
            $sync_site_log_data = [];
            $sync_site_log_data['site_id'] = $siteInfo->id;
            $sync_site_log_data['site_name'] = $siteInfo->name;
            $sync_site_log_data['event_type'] = $sync_type;
            $sync_site_log_data['event_name'] = NotityTypeConst::$typeMap[$sync_type] ?? '';
            $sync_site_log_data['status'] = 1;
            switch ($sync_type) {
                case NotityTypeConst::SYNC_SITE_PRICE:
                    $this->syncSitePrice($siteInfo);
                    break;
                case NotityTypeConst::SYNC_SITE_COUNTRY:
                    $this->syncSiteCountry($siteInfo);
                    break;
                case NotityTypeConst::SYNC_SITE_LANGUAGE:
                    $this->syncSiteLanguage($siteInfo);
                    break;
                case NotityTypeConst::SYNC_SITE_IP_WHITE:
                    $this->syncSiteIpWhite($siteInfo);
                    break;
                case NotityTypeConst::SYNC_SITE_SETTING:
                    $this->syncSiteSetting($siteInfo);
                    break;
                case NotityTypeConst::SYNC_SITE_PUBLISHER:
                    $this->syncSitePublisher($siteInfo);
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            $errData = [
                'data'  => $this->data,
                'error' => $e->getMessage(),
            ];
            $sync_site_log_data['status'] = 0;
            \Log::error('同步总控数据--错误信息与数据:'.json_encode($errData));
        }
        $this->useLocalDb();
        SyncSiteLog::query()->create($sync_site_log_data);

        return true;
    }

    public function syncSitePrice($siteInfo) {
        //同步 price_editions
        $priceeditionList = AdminPriceEdition::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        $database_info = $this->getDbConfigBySite($siteInfo);
        $this->useRemoteDbBySite($database_info);
        foreach ($priceeditionList as $forPriceEdition) {
            $for_id = $forPriceEdition['id'];
            $existIdList[] = $for_id;
            $isExist = DB::connection('mysql')->table('price_editions')->where("id", $for_id)->count();
            if ($isExist) {
                // 存在则更新
                DB::connection('mysql')->table('price_editions')->where("id", $for_id)->update($forPriceEdition);
            } else {
                DB::connection('mysql')->table('price_editions')->insert($forPriceEdition);
            }
        }
        DB::connection('mysql')->table('price_editions')->whereNotIn("id", $existIdList)->delete();
        //同步 price_edition_values
        $this->useLocalDb();
        $priceValueList = AdminPriceEditionValue::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        $this->useRemoteDbBySite($database_info);
        foreach ($priceValueList as $forPriceValue) {
            $for_id = $forPriceValue['id'];
            $existIdList[] = $for_id;
            $isExist = DB::connection('mysql')->table('price_edition_values')->where("id", $for_id)->count();
            if ($isExist) {
                // 存在则更新
                DB::connection('mysql')->table('price_edition_values')->where("id", $for_id)->update($forPriceValue);
            } else {
                DB::connection('mysql')->table('price_edition_values')->insert($forPriceValue);
            }
        }
        DB::connection('mysql')->table('price_edition_values')->whereNotIn("id", $existIdList)->delete();
    }

    public function syncSiteCountry($siteInfo) {
        //同步 countrys
        $coutryList = AdminCountry::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        $database_info = $this->getDbConfigBySite($siteInfo);
        $this->useRemoteDbBySite($database_info);
        foreach ($coutryList as $forCoutry) {
            unset($forCoutry['id']);
            $site_contory_id = DB::connection('mysql')->table('countrys')
                                 ->where("name", $forCoutry['name'])
                                 ->value("id");
            if (!empty($site_contory_id)) {
                $existIdList[] = $site_contory_id;
                // 存在则更新
                DB::connection('mysql')->table('countrys')->where("id", $site_contory_id)->update($forCoutry);
            } else {
                $site_contory_id = DB::connection('mysql')->table('countrys')->insertGetId($forCoutry);
                $existIdList[] = $site_contory_id;
            }
        }
        DB::connection('mysql')->table('countrys')->whereNotIn("id", $existIdList)->delete();
    }

    public function syncSiteLanguage($siteInfo) {
        //同步 Language
        $languageList = AdminLanguage::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        $database_info = $this->getDbConfigBySite($siteInfo);
        $this->useRemoteDbBySite($database_info);
        foreach ($languageList as $forCoutry) {
            $for_id = $forCoutry['id'];
            $existIdList[] = $for_id;
            $isExist = DB::connection('mysql')->table('languages')->where("id", $for_id)->count();
            if ($isExist) {
                // 存在则更新
                DB::connection('mysql')->table('languages')->where("id", $for_id)->update($forCoutry);
            } else {
                DB::connection('mysql')->table('languages')->insert($forCoutry);
            }
        }
        DB::connection('mysql')->table('languages')->whereNotIn("id", $existIdList)->delete();
    }

    public function syncSiteSetting($siteInfo) {
        //同步 System SystemValues
        //只控制ip限流与ua限流配置
        $systemList = AdminSystem::query()->whereIn("alias", ['ip_limit_rules', 'ua_ban_rule', 'nginx_ban_rules',
                                                              'access_cnt_nginx_ban'])->get()->map(
            function ($item) {
                return $item->getAttributes();
            }
        )->toArray();
        $systemIdList = [];
        $database_info = $this->getDbConfigBySite($siteInfo);
        $this->useRemoteDbBySite($database_info);
        foreach ($systemList as $forCoutry) {
            $for_id = $forCoutry['id'];
            $systemIdList[] = $for_id;
            $idExist = DB::connection('mysql')->table('systems')->where("alias", $forCoutry['alias'])->value('id');
            unset($forCoutry['id']);
            //$forCoutry['updated_at'] = time();
            if ($idExist > 0) {
                // 存在则更新
                DB::connection('mysql')->table('systems')->where("id", $idExist)->update($forCoutry);
            } else {
                DB::connection('mysql')->table('systems')->insert($forCoutry);
            }
        }
        ######################################
        $this->useLocalDb();
        $systemValueList = AdminSystemValue::query()->whereIn("parent_id", $systemIdList)->get()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        foreach ($systemValueList as &$forSystemValue) {
            $for_id = $forSystemValue['id'];
            unset($forSystemValue['id']);
            //parentid需要修改
            $this->useLocalDb();
            $parentAlias = AdminSystem::query()->where("id", $forSystemValue['parent_id'])->value('alias');
            $this->useRemoteDbBySite($database_info);
            $forSystemValue['parent_id'] = DB::connection('mysql')->table('systems')->where("alias", $parentAlias)
                                             ->value('id');
            DB::connection('mysql')->table('system_values')->where("parent_id", $forSystemValue['parent_id'])->delete();
        }
        foreach ($systemValueList as $forsSystemValue) {
            $siteSysId = DB::connection('mysql')->table('system_values')->where("key", $forsSystemValue['key'])->value(
                'id'
            );
            unset($forsSystemValue['id']);
            //$forsSystemValue['updated_at'] = time();
            if ($siteSysId > 0) {
                // 存在则更新
                DB::connection('mysql')->table('system_values')->where("id", $siteSysId)->update($forsSystemValue);
            } else {
                DB::connection('mysql')->table('system_values')->insert($forsSystemValue);
            }
        }
    }

    public function syncSiteIpWhite($siteInfo) {
        //同步 BanWhite
        $BanWhiteList = AdminBanWhiteList::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        $database_info = $this->getDbConfigBySite($siteInfo);
        $this->useRemoteDbBySite($database_info);
        foreach ($BanWhiteList as $forCoutry) {
            $for_id = $forCoutry['id'];
            $existIdList[] = $for_id;
            $isExist = DB::connection('mysql')->table('ban_white_list')->where("id", $for_id)->count();
            $forCoutry['updated_at'] = time();
            if ($isExist) {
                // 存在则更新
                DB::connection('mysql')->table('ban_white_list')->where("id", $for_id)->update($forCoutry);
            } else {
                DB::connection('mysql')->table('ban_white_list')->insert($forCoutry);
            }
        }
        DB::connection('mysql')->table('ban_white_list')->whereNotIn("id", $existIdList)->delete();
    }

    public function syncSitePublisher($siteInfo) {
        $publisher_ids = $siteInfo['publisher_id'];
        $publisher_id_list = explode(',', $publisher_ids);
        //同步 Publisher
        $publisherList = Publisher::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        $database_info = $this->getDbConfigBySite($siteInfo);
        $this->useRemoteDbBySite($database_info);
        foreach ($publisherList as $forCoutry) {
            $for_id = $forCoutry['id'];
            if (!in_array($for_id, $publisher_id_list)) {
                continue;
            }
            $existIdList[] = $for_id;
            $isExist = DB::connection('mysql')->table('publishers')->where("id", $for_id)->count();
            $forCoutry['updated_at'] = time();
            if ($isExist) {
                // 存在则更新
                DB::connection('mysql')->table('publishers')->where("id", $for_id)->update($forCoutry);
            } else {
                DB::connection('mysql')->table('publishers')->insert($forCoutry);
            }
        }
        DB::connection('mysql')->table('publishers')->whereNotIn("id", $existIdList)->delete();
    }

    public function getDbConfigBySite($site) {
        $database_id = $site['database_id'];
        $database_info = Database::find($database_id);
        if (empty($database_info)) {
            return [];
        } else {
            return $database_info->toArray();
        }
    }

    private function useLocalDb() {
        // 切换到新的数据库配置
        $mysql = "mysql";
        Config::set("database.connections.{$mysql}", $this->sourceDbConfig);
        // 断开当前连接
        DB::purge($mysql);
        // 重新连接
        DB::reconnect($mysql);

        return $mysql;
    }

    private function useRemoteDbBySite($database_info) {
        // 定义新的数据库配置
        $newDatabaseConfig = [
            'driver'    => 'mysql',
            'host'      => $database_info['public_host'],
            'database'  => $database_info['name'],
            'username'  => $database_info['username'],
            'password'  => $database_info['password'],
            'port'      => '3306',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ];
        // 切换到新的数据库配置
        $mysql = "mysql";
        Config::set("database.connections.{$mysql}", $newDatabaseConfig);
        // 断开当前连接
        DB::purge($mysql);
        // 重新连接
        DB::reconnect($mysql);

        return $mysql;
    }
}
