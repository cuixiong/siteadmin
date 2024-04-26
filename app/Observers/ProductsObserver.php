<?php

namespace App\Observers;

use Illuminate\Support\Facades\Redis;
use Modules\Site\Http\Models\ProductsDescription;

class ProductsObserver {
    const REDISPRODUCTSTTL = 86400; //过期时间5天

    /**
     * 处理「创建」事件。
     *
     * @return void
     */
    public function created($model) {
    }

    /**
     * 处理「更新」事件。
     *
     * @return void
     */
    public function updated($model) {
        if (!empty($model)) {
            $redisKey = $this->getRedisKey($model);
            $dataArr = $model->toArray();
            Redis::set($redisKey, json_encode($dataArr));

            //删除描述详情的缓存
            $this->delProductsDescRedis($model);
        }
    }

    public function saved($model) {
        if (!empty($model)) {
            $redisKey = $this->getRedisKey($model);
            $dataArr = $model->toArray();
            Redis::set($redisKey, json_encode($dataArr));

            //删除描述详情的缓存
            $this->delProductsDescRedis($model);
        }
    }

    /**
     * 处理「删除」事件。
     *
     * @return void
     */
    public function deleted($model) {
        if (!empty($model)) {
            $redisKey = $this->getRedisKey($model);
            Redis::del($redisKey);

            //删除描述详情的缓存
            $this->delProductsDescRedis($model);
        }
    }

    /**
     *
     * @param $model
     *
     * @return string
     */
    public function getRedisKey($model): string {
        $site = request()->header('Site', '');
        $redisKey = $site."_".class_basename($model)."_".$model->getKey();

        return $redisKey;
    }


    public function getPDescRedisKey($model): string {
        $site = request()->header('Site', '');
        $redisKey = $site."_".class_basename(ProductsDescription::class)."_".$model->getKey();

        return $redisKey;
    }

    /**
     *
     * @param $model
     *
     */
    private function delProductsDescRedis($model): void {
        $redisPDescKey = $this->getPDescRedisKey($model);
        Redis::del($redisPDescKey);
    }
}
