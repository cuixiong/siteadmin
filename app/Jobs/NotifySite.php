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
use Modules\Admin\Http\Models\Country as AdminCountry;
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

    public $data = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data) {
        $this->data = $data;
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
            $siteInfo = Site::query()->where('id', $data['siteId'])->first();
            if (empty($siteInfo) || $siteInfo->status != 1) {
                \Log::error('返回结果数据:同步错误  文件路径:'.__CLASS__.'  行号:'.__LINE__);

                return true;
            }
            $sync_type = $data['sync_type'];
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
                default:
                    break;
            }
        } catch (\Exception $e) {
            $errData = [
                'data'  => $this->data,
                'error' => $e->getMessage(),
            ];
            \Log::error('同步总控数据--错误信息与数据:'.json_encode($errData));
        }

        return true;
    }

    public function syncSitePrice($siteInfo) {
        // TODO: cuizhixiong 2024/9/20 后续需考虑国外站点的同步
        if (empty($siteInfo['is_local'])) {
            return true;
        }
        // 设置当前租户
        tenancy()->initialize($siteInfo['name']);
        //同步 price_editions
        $priceeditionList = AdminPriceEdition::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        foreach ($priceeditionList as $forPriceEdition) {
            $for_id = $forPriceEdition['id'];
            $existIdList[] = $for_id;
            $isExist = PriceEdition::query()->where("id", $for_id)->count();
            if ($isExist) {
                // 存在则更新
                PriceEdition::query()->where("id", $for_id)->update($forPriceEdition);
            } else {
                PriceEdition::insert($forPriceEdition);
            }
        }
        PriceEdition::query()->whereNotIn("id", $existIdList)->delete();
        //同步 price_edition_values
        $priceValueList = AdminPriceEditionValue::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        foreach ($priceValueList as $forPriceValue) {
            $for_id = $forPriceValue['id'];
            $existIdList[] = $for_id;
            $isExist = PriceEditionValue::query()->where("id", $for_id)->count();
            if ($isExist) {
                // 存在则更新
                PriceEditionValue::query()->where("id", $for_id)->update($forPriceValue);
            } else {
                PriceEditionValue::insert($forPriceValue);
            }
        }
        PriceEditionValue::query()->whereNotIn("id", $existIdList)->delete();
    }

    public function syncSiteCountry($siteInfo) {
        // TODO: cuizhixiong 2024/9/20 后续需考虑国外站点的同步
        if (!$siteInfo['is_local']) {
            return true;
        }
        // 设置当前租户
        tenancy()->initialize($siteInfo['name']);
        //同步 price_editions
        $coutryList = AdminCountry::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        foreach ($coutryList as $forCoutry) {
            $for_id = $forCoutry['id'];
            $existIdList[] = $for_id;
            $isExist = Country::query()->where("id", $for_id)->count();
            if ($isExist) {
                // 存在则更新
                Country::query()->where("id", $for_id)->update($forCoutry);
            } else {
                Country::insert($forCoutry);
            }
        }
        Country::query()->whereNotIn("id", $existIdList)->delete();
    }

    public function syncSiteLanguage($siteInfo) {
        // TODO: cuizhixiong 2024/9/20 后续需考虑国外站点的同步
        if (!$siteInfo['is_local']) {
            return true;
        }
        // 设置当前租户
        tenancy()->initialize($siteInfo['name']);
        //同步 price_editions
        $languageList = AdminLanguage::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        foreach ($languageList as $forCoutry) {
            $for_id = $forCoutry['id'];
            $existIdList[] = $for_id;
            $isExist = Language::query()->where("id", $for_id)->count();
            if ($isExist) {
                // 存在则更新
                Language::query()->where("id", $for_id)->update($forCoutry);
            } else {
                Language::insert($forCoutry);
            }
        }
        Language::query()->whereNotIn("id", $existIdList)->delete();
    }

    public function syncSiteSetting($siteInfo) {
        // TODO: cuizhixiong 2024/9/20 后续需考虑国外站点的同步
        if (!$siteInfo['is_local']) {
            return true;
        }
        // 设置当前租户
        tenancy()->initialize($siteInfo['name']);
        //同步 price_editions
        //只控制ip限流与ua限流配置
        $systemList = AdminSystem::query()->whereIn("alias", ['ip_limit_rules', 'ua_ban_rule'])->get()->map(
            function ($item) {
                return $item->getAttributes();
            }
        )->toArray();
        $systemIdList = [];
        foreach ($systemList as $forCoutry) {
            $for_id = $forCoutry['id'];
            $systemIdList[] = $for_id;
            $idExist = System::query()->where("alias", $forCoutry['alias'])->value('id');
            if ($idExist > 0) {
                // 存在则更新
                System::query()->where("id", $idExist)->update($forCoutry);
            } else {
                System::insert($forCoutry);
            }
        }
        ######################################
        $systemValueList = AdminSystemValue::query()->whereIn("parent_id", $systemIdList)->get()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        foreach ($systemValueList as $forSystemValue) {
            $for_id = $forSystemValue['id'];
            unset($forSystemValue['id']);
            //parentid需要修改
            $parentAlias = AdminSystem::query()->where("id", $forSystemValue['parent_id'])->value('alias');
            $forSystemValue['parent_id'] = System::query()->where("alias", $parentAlias)->value('id');
            $siteSysId = SystemValue::query()->where("key", $forSystemValue['key'])->value('id');
            if ($siteSysId > 0) {
                // 存在则更新
                SystemValue::query()->where("id", $siteSysId)->update($forSystemValue);
            } else {
                SystemValue::insert($forSystemValue);
            }
        }
    }

    public function syncSiteIpWhite($siteInfo) {
        // TODO: cuizhixiong 2024/9/20 后续需考虑国外站点的同步
        if (!$siteInfo['is_local']) {
            return true;
        }
        // 设置当前租户
        tenancy()->initialize($siteInfo['name']);
        //同步 price_editions
        $BanWhiteList = AdminBanWhiteList::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        $existIdList = [];
        foreach ($BanWhiteList as $forCoutry) {
            $for_id = $forCoutry['id'];
            $existIdList[] = $for_id;
            $isExist = BanWhiteList::query()->where("id", $for_id)->count();
            if ($isExist) {
                // 存在则更新
                BanWhiteList::query()->where("id", $for_id)->update($forCoutry);
            } else {
                BanWhiteList::insert($forCoutry);
            }
        }
        BanWhiteList::query()->whereNotIn("id", $existIdList)->delete();
    }
}
