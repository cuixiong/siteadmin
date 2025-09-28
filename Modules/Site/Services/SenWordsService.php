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

use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\SensitiveWords;
use Modules\Site\Http\Models\SensitiveWordsHandleLog;
use Modules\Site\Http\Models\SensitiveWordsLog;

class SenWordsService
{
    public static $senWords;  //关键词数组

    public static function getSenWords()
    {
        if (empty(self::$senWords)) {
            self::$senWords = SensitiveWords::query()->where("status", \App\Const\CommonConst::CONST_NORMAL_STATUS)
                ->pluck("word")->toArray();
        }

        return self::$senWords;
    }

    /**
     * 校验是否存在敏感词(批量上传、同步)
     *
     * @param $content
     *
     * @return bool
     */
    public static function checkFitter($content)
    {
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
     * 校验是否存在敏感词(报告增改)
     * 
     * @param $name
     */
    public static function checkNewFitter($name)
    {
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

    /**
     * 处理涉及文本的报告以及课题
     * @param string|array $words 敏感词 
     * @param $type ; type = 1 返回需要处理的报告数量、课题数量; type = 2 执行处理报告、课题
     * @param $site 站点，用于判断需要过滤是哪个字段
     * 
     */
    public function hiddenData($sence, $type, $words, $site, $oldWords = [])
    {


        // $ids = [];
        // if (!empty($id) && is_array($id) && count($id) > 0) {
        //     $ids = $id;
        // } elseif (!empty($word)) {
        //     $ids = [$id];
        // }

        $wordsArray = [];
        if (!empty($words) && is_array($words) && count($words) > 0) {
            $wordsArray = $words;
        } elseif (!empty($words)) {
            $wordsArray = [$words];
        } else {
            // return ['res' => false, 'msg' => '未传入敏感词'];
        }


        // $sensitiveWordsData = SensitiveWords::query()->select(['id', 'word'])->whereIn('id', $ids)->get();
        // if ($sensitiveWordsData) {
        //     $sensitiveWordsData = $sensitiveWordsData->toArray();
        //     $wordsArray = array_column($sensitiveWordsData, 'word');
        //     $sensitiveWordsData = array_column($sensitiveWordsData, null, 'id');
        // } else {
        //     return ['res' => false, 'msg' => '查询数据缺少敏感词'];
        // }

        $oldWordsArray = [];
        if (!empty($oldWords) && is_array($oldWords) && count($oldWords) > 0) {
            $oldWordsArray = $oldWords;
        } elseif (!empty($oldWords)) {
            $oldWordsArray = [$oldWords];
        } else {
            // return ['res' => false, 'msg' => '未传入敏感词'];
        }

        $field = 'english_name';
        if (in_array($site, ['qycojp', 'yhcojp', 'lpijp', 'girjp', 'qyjp'])) {
            $field = 'name';
        }

        // 报告基本查询
        $productBaseQuery = null;
        if (count($wordsArray) > 0) {
            $productBaseQuery =  Products::query()->where("status", 1)
                ->where(function ($query) use ($wordsArray, $field) {
                    foreach ($wordsArray as $value) {
                        $query->orWhere($field, 'like', "%{$value}%");
                        $query->orWhere('url', 'like', "%{$value}%");
                    }
                });
        }

        // 恢复报告状态基本查询
        $oldProductBaseQuery = null;
        if (count($oldWordsArray) > 0) {
            $oldProductBaseQuery = Products::query()->where("status", 0)
                ->where(function ($query) use ($oldWordsArray, $field) {
                    foreach ($oldWordsArray as $value) {
                        $query->orWhere($field, 'like', "%{$value}%");
                        $query->orWhere('url', 'like', "%{$value}%");
                    }
                });
        }
        // 课题基本查询
        $subjectBaseQuery = null;
        if (count($wordsArray) > 0) {
            $subjectBaseQuery =  PostSubject::query()
                ->where(function ($query) use ($wordsArray, $field) {
                    foreach ($wordsArray as $value) {
                        $query->orWhere('name', 'like', "%{$value}%");
                    }
                });
        }

        if ($type == 1) {
            $productCount = $productBaseQuery ? $productBaseQuery->count() : 0;
            $subjectCount =  $subjectBaseQuery ? $subjectBaseQuery->count() : 0;
            $oldProductCount = 0;
            if (count($oldWordsArray) > 0 && $oldProductBaseQuery) {
                $oldProductCount = $oldProductBaseQuery->count();
            }
            return [
                'product_count' => $productCount,
                'old_product_count' => $oldProductCount,
                'subject_count' => $subjectCount
            ];
        } elseif ($type == 2) {
            $productHiddenCount = 0;
            $productShowCount = 0;
            $subjectDeleteCount = 0;
            $productHiddenDetails = '';
            $productShowDetails = '';
            $subjectDeleteDetails = '';
            // 将报告状态关闭
            if ($productBaseQuery) {
                $productData = $productBaseQuery->select(['id', 'name'])->get();
                if ($productData && count($productData) > 0) {
                    $productHiddenCount = count($productData);
                    $productData = $productData->toArray();
                    $productIds = array_column($productData, 'id');
                    Products::query()->whereIn('id', $productIds)->update(['status' => 0]);
                    //删除sphinx的索引
                    (new SphinxService())->updateSphinxStatusByProductIdList($productIds, 0);
                    foreach ($productData as $key => $item) {
                        $productHiddenDetails .= "【报告id-" . $item['id'] . "】" . $item['name'] . "\n";
                    }
                }
            }
            // 若是修改场景，将原有报告状态开启
            if (count($oldWordsArray) > 0 && $oldProductBaseQuery) {
                $oldProductData = $oldProductBaseQuery->select(['id', 'name'])->get();
                if ($oldProductData && count($oldProductData) > 0) {
                    $productShowCount = count($oldProductData);
                    $oldProductData = $oldProductData->toArray();
                    $oldProductIds = array_column($oldProductData, 'id');
                    Products::query()->whereIn('id', $oldProductIds)->update(['status' => 1]);
                    (new SphinxService())->updateSphinxStatusByProductIdList($oldProductIds, 1);

                    foreach ($oldProductData as $key => $item) {
                        $productShowDetails .= "【报告id-" . $item['id'] . "】" . $item['name'] . "\n";
                    }
                }
            }

            // 涉及课题需要全删，包括已宣传
            if ($subjectBaseQuery) {
                $subjectDeleteData = $subjectBaseQuery->select(['id', 'name'])->get();
                if ($subjectDeleteData && count($subjectDeleteData) > 0) {
                    $subjectDeleteCount = count($subjectDeleteData);
                    $subjectDeleteData = $subjectDeleteData->toArray();
                    $subjectDeleteIds = array_column($subjectDeleteData, 'id');
                    $subjectDeleteCount = PostSubject::whereIn('id', $subjectDeleteIds)->delete();
                    foreach ($subjectDeleteData as $key => $item) {
                        $subjectDeleteDetails .= "【课题id-" . $item['id'] . "】" . $item['name'] . "\n";
                    }
                }
            }
            // 加入日志
            $logData = [];
            $logData['log_type'] = $sence;
            $logData['words'] = implode("\n", $wordsArray);
            $logData['old_words'] = implode("\n", $oldWordsArray);
            $logData['product_hidden_count'] = $productHiddenCount;
            $logData['product_show_count'] = $productShowCount;
            $logData['subject_delete_count'] = $subjectDeleteCount;
            $logData['product_hidden_details'] = $productHiddenDetails;
            $logData['product_show_details'] = $productShowDetails;
            $logData['subject_delete_details'] = $subjectDeleteDetails;
            SensitiveWordsHandleLog::create($logData);
            return true;
        } else {
            return false;
        }
    }


    public function getProductIdListByWord($word)
    {
        $productIdList = Products::query()->where("status", 1)
            ->where(function ($query) use ($word) {
                $query->orWhere('english_name', 'like', "%{$word}%");
                $query->orWhere('url', 'like', "%{$word}%");
            })->pluck('id')->toArray();

        return $productIdList;
    }

    public function handlerUnBanByIdList($wordId)
    {
        $productIdList = SensitiveWordsLog::query()->where("word_id", $wordId)->pluck('product_id')->toArray();
        $res = Products::query()->whereIn('id', $productIdList)->update(['status' => 1]);
        $product_model = new Products();
        foreach ($productIdList as $forProductId) {
            $product_model->syncSearchIndex($forProductId, 'update');
        }
        //删除日志
        SensitiveWordsLog::query()->where("word_id", $wordId)->delete();
    }

    public function handlerBanByIdList($word_id)
    {
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

    public function addSenWokrdLogByProductList($product_List)
    {
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
