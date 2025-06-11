<?php
/**
 * SenWordsService.php UTF-8
 * 敏感关键词过滤业务
 *
 * @date    : 2024/5/8 11:46 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Services;

use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\SensitiveWords;
use Modules\Site\Http\Models\SensitiveWordsLog;

class SenWordsService {
    public static $senWords;  //关键词数组

    public static function getSenWords() {
        if (empty(self::$senWords)) {
            self::$senWords = SensitiveWords::query()->where("status", \App\Const\CommonConst::CONST_NORMAL_STATUS)
                                            ->pluck("word")->toArray();
        }

        return self::$senWords;
    }

    /**
     * 校验是否存在敏感词
     *
     * @param $content
     *
     * @return bool
     */
    public static function checkFitter($content) {
        $senWordsList = self::getSenWords();
        $checkRes = false;
        foreach ($senWordsList as $fillterRules) {
            if (mb_strpos($content, $fillterRules) !== false) {
                $checkRes = true;
                break;
            }
        }

        return $checkRes;
    }

    /**
     * @param       $name
     */
    public static function checkNewFitter($name) {
        $senWordsList = self::getSenWords();
        $checkRes = false;
        foreach ($senWordsList as $fillterRules) {
            //if (mb_strpos($name, $fillterRules) !== false) { //中文比对
            if (strpos($name, $fillterRules) !== false) { //是否包含
                $checkRes = $fillterRules;
                break;
            }
        }

        return $checkRes;
    }

    public function getProductIdListByWord($word) {
        $productIdList = Products::query()->where("status", 1)
                                 ->where(function ($query) use ($word) {
                                     $query->orWhere('english_name', 'like', "%{$word}%");
                                     $query->orWhere('url', 'like', "%{$word}%");
                                 })->pluck('id')->toArray();

        return $productIdList;
    }

    public function handlerUnBanByIdList($wordId) {
        $productIdList = SensitiveWordsLog::query()->where("word_id", $wordId)->pluck('product_id')->toArray();
        $res = Products::query()->whereIn('id', $productIdList)->update(['status' => 1]);
        $product_model = new Products();
        foreach ($productIdList as $forProductId) {
            $product_model->syncSearchIndex($forProductId, 'add');
        }
        //删除日志
        SensitiveWordsLog::query()->where("word_id", $wordId)->delete();
    }

    public function handlerBanByIdList($word_id) {
        $word = SensitiveWords::query()->where('id', $word_id)->value('word');
        if (empty($word)) {
            return false;
        }
        $productIdList = $this->getProductIdListByWord($word);
        $product_list = Products::query()->whereIn("id", $productIdList)
                                ->select(['id', 'url', 'english_name'])
                                ->get()->toArray();
        $res = Products::query()->whereIn('id', $productIdList)->update(['status' => 0]);
        if (!empty($productIdList)) {
            //删除sphinx的索引
            (new SphinxService())->delSphinxIndexByProductIdList($productIdList);
        }
        foreach ($product_list as $forproduct) {
            $data = [
                'word_id'      => $word_id,
                'word'         => $word,
                'product_id'   => $forproduct['id'],
                'product_url'  => $forproduct['url'],
                'product_name' => $forproduct['english_name'],
            ];
            SensitiveWordsLog::create($data);
        }
    }

    public function addSenWokrdLogByProductList($product_List) {
        $product_list = Products::query()->whereIn("id", $product_List)
                                ->select(['id', 'url', 'english_name'])
                                ->get()->toArray();
        $sensitiveWordsList = SensitiveWords::query()->where("status", 1)
                                            ->get()->toArray();
        foreach ($product_list as $forproduct) {
            $word_id = 0;
            $word = '';
            foreach ($sensitiveWordsList as $sensitiveWordsInfo) {
                if (strpos($forproduct['english_name'], $sensitiveWordsInfo['word']) !== false) { //是否包含
                    $word_id = $sensitiveWordsInfo['id'];
                    $word = $sensitiveWordsInfo['word'];
                    break;
                }
                if (strpos($forproduct['url'], $sensitiveWordsInfo['word']) !== false) { //是否包含
                    $word_id = $sensitiveWordsInfo['id'];
                    $word = $sensitiveWordsInfo['word'];
                    break;
                }
            }
            if (empty($word) || empty($word_id)) {
                continue;
            }
            $data = [
                'word_id'      => $word_id,
                'word'         => $word,
                'product_id'   => $forproduct['id'],
                'product_url'  => $forproduct['url'],
                'product_name' => $forproduct['english_name'],
            ];
            SensitiveWordsLog::create($data);
        }

    }
}
