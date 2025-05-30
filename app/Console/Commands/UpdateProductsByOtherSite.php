<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Admin\Http\Models\Database;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Controllers\SyncThirdProductController;
use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\SyncLog;
use Modules\Site\Http\Models\SystemValue;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;
use Modules\Site\Http\Models\TemplateCateMapping;

class UpdateProductsByOtherSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:updateByOtherSite {--site=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $signKey = '62d9048a8a2ee148cf142a0e6696ab26';

    public static $autoSyncDataKey = 'autoSyncData';
    public static $openAutoSyncData       = 1;
    public static $closeAutoSyncData      = 0;

    /**
     * 日文网站从英文网站获取报告数据
     * 1、lpi-jp没有数据，要从lpi-en那边获取，跨地区连数据库获取太慢，改为lpi-en网站提供接口;
     * 2、lpi-en含日文关键词的数据太少，所以还需去qy-jp网站查询填补日语关键词;
     *
     * @return int
     */
    public function handle()
    {


        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", -1);
        $start_time  = time();
        $count = 0;
        $insertCount = 0;
        $updateCount = 0;
        $ingoreCount = 0;
        $errorCount = 0;
        $details = '';
        $ingore_detail = '';
        $updateDetail = '';
        $insertDetail = '';
        $succIdList = [];

        $option = $this->option();
        $originSiteName = $option['site'];
        if (empty($originSiteName)) {
            echo '参数异常' . PHP_EOL;
            exit;
        }

        tenancy()->initialize($originSiteName);

        $autoSyncDataVal = SystemValue::query()
            ->where("key", self::$autoSyncDataKey)
            ->value('hidden');
        //如果没有开启自动同步开关, 那么直接退出
        if (empty($autoSyncDataVal) && $autoSyncDataVal != self::$openAutoSyncData) {
            echo '未启动网站设置的开关' . "\n";
            exit;
        }

        $startTimestamp = SystemValue::where('key', 'sync_start_timestamp')->value('value');
        $productDataUrl = SystemValue::where('key', 'sync_get_product_url')->value('value');
        $keywordsUrl = SystemValue::where('key', 'sync_keywords_url')->value('value');
        $queryNum = SystemValue::where('key', 'sync_query_num')->value('value');
        $startProductId = SystemValue::where('key', 'sync_start_product_id')->value('value');
        // $keywordsField = SystemValue::where('key', 'sync_keywords_field')->value('value');

        // dd($productDataUrl);
        // exit;
        // echo $url;
        // $domain = 'https://www.qyresearch.com/';
        // $url = $domain . '/api/third/get-product-data';

        // $url = 'https://www.lpinformationdata.com/test/get-product-data';
        // dd($startTimestamp);
        // dd($url);
        // exit;
        if (empty($productDataUrl) || empty($keywordsUrl)) {
            echo '缺少地址' . "\n";
            exit;
        }
        if (empty($queryNum)) {
            echo '缺少查询数量' . "\n";
            exit;
        }

        // 查询目标网站的报告数据
        $reqData = [
            'startTimestamp'    => $startTimestamp,
            'num'    => $queryNum,
            'startProductId' => $startProductId,
        ];
        $reqData['sign'] = $this->makeSign($reqData, $this->signKey);
        $response = Http::post($productDataUrl, $reqData);
        // $response = Http::withHeaders([
        //     'Accept' => 'application/json',
        //     'Content-Type' => 'application/x-www-form-urlencoded',
        // ])->post($productDataUrl, $reqData);
        $resp = $response->json();
        if (empty($resp) || $resp['code'] != 200) {
            echo '请求数据失败' . "\n" . json_encode($resp) . "\n";
            exit;
        } elseif (empty($resp) || $resp['code'] == 500) {
            echo $resp['msg'] . "\n";
            exit;
        }

        $productData = $resp['data'];
        if (empty($productData)) {
            echo '请求无数据' . "\n";
            exit;
        }
        $count = count($productData);
        $lastUpdateTime = strtotime(end($productData)['updated_at']); // 记录最后一条数据的更新时间，下一次从此时间戳开始查询
        $lastProductId = end($productData)['id'];

        // 需要去另一个网站上查询缺失的日文关键词

        $urlArray = []; //自定义链接数组
        foreach ($productData as $key => $item) {
            if (empty($item['keywords_jp'])) {
                $urlArray[] = $item['url'];
            }
        }
        // dd($urlArray);
        // exit;
        $urlArray = array_unique($urlArray);
        $keywordsArray = [];
        if ($urlArray && count($urlArray) > 0) {

            $urlArrayParam = json_encode($urlArray);
            $reqData = [
                'url_data'    => $urlArrayParam,
            ];
            $reqData['sign'] = $this->makeSign($reqData, $this->signKey);
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->post($keywordsUrl, $reqData);
            $resp = $response->json();
            if (empty($resp) || $resp['code'] != 200) {
                echo '请求更新关键词接口失败' . "\n" . json_encode($resp) . "\n";
            }
            $keywordsArray = $resp['data'];
        }
        // dd($keywordsArray);
        // exit;

        $productNameArray = [];
        $productNameArray = array_column($productData, 'name');
        //默认出版商
        $publisherIds = Site::where('name', $originSiteName)->value('publisher_id');
        $publisherIdArray = explode(',', $publisherIds);
        $defaultPublisherId = $publisherIdArray[0];

        // 行业
        $categoryData = ProductsCategory::query()->select(["id", "link"])->where("status", 1)->get()->toArray();
        $categoryData = array_column($categoryData, 'id', 'link');
        // dd($categoryData);
        // exit;

        // 默认标题模板
        $templateTitleCache = [];
        $templateCategory = TemplateCategory::query()->select(['id', 'match_words'])->where(['status' => 1])->orderBy('sort', 'ASC')->orderBy('id', 'DESC')->get()->toArray();


        // 去重
        $existData = Products::query()->select(['id', 'english_name as name', 'published_date'])->whereIn('english_name', $productNameArray)->get()->toArray();
        $existNameArray = array_column($existData, 'name');
        $existData = array_column($existData, null, 'name');
        foreach ($productData as $key => $item) {
            if (empty($item['name'])) {
                $ingoreCount++;
                continue;
            }

            //填充到原来的报告数据中
            if (isset($keywordsArray[$item['url']])) {
                $item['keywords_jp'] = $keywordsArray[$item['url']];
            }

            if (empty($item['keywords_jp'])) {
                $ingoreCount++;
                $ingore_detail .= "【忽略】报告名:{$item['name']};url:{$item['url']};无法查询到关键词" . "\r\n";
                continue;
            }

            // 行业转换
            if (isset($categoryData[$item['category_link']]) && $categoryData[$item['category_link']]) {
                $item['category_id'] = $categoryData[$item['category_link']];
            } else {
                $item['category_id'] = 0;
            }


            $defaultTemplateCategory = 0;
            // 获取该条数据所属模板分类
            foreach ($templateCategory as $templateCategoryItem) {
                if (empty($templateCategoryItem['match_words'])) {
                    if ($defaultTemplateCategory == 0) {
                        $defaultTemplateCategory = $templateCategoryItem['id'];
                    }
                    continue;
                }
                $templateCategorykeywords = explode(',', $templateCategoryItem['match_words']);
                //只需满足任意关键词
                $flag = false;
                foreach ($templateCategorykeywords as $categorykeyword) {
                    if (strpos($item['description'], $categorykeyword) !== false) {
                        $flag = true;
                        break;
                    }
                }
                if ($flag) {
                    $defaultTemplateCategory = $templateCategoryItem['id'];
                    break;
                }
            }

            $templateTitle = '';
            if (isset($templateTitleCache[$defaultTemplateCategory])) {

                $templateTitle = $templateTitleCache[$defaultTemplateCategory] ?? '';
            } else {
                $templateTitle = Template::from((new Template)->getTable() . ' as t')->select(['content'])
                    ->where(['status' => 0])
                    ->where(['type' => 2])
                    ->leftJoin((new TemplateCateMapping)->getTable() . ' as tcp', function ($join) {
                        $join->on('tcp.temp_id', '=', 't.id');
                    })
                    ->where('cate_id', $defaultTemplateCategory)
                    ->orderBy('t.sort', 'DESC')->orderBy('t.id', 'DESC')
                    ->value('content');

                $templateTitleCache[$defaultTemplateCategory] = $templateTitle;
            }
            // dd($templateTitle);
            // exit;
            // 2.0兼容
            if (!isset($item['keywords']) && isset($item['keyword'])) {
                $item['keywords'] = $item['keyword'];
            }
            if (!isset($item['price']) && isset($item['single_user_price'])) {
                $item['price'] = $item['single_user_price'];
            }
            $newProductName = $templateTitle;
            $newProductName = str_replace('@@@@', $item['keywords_jp'], $newProductName);
            $newProductName = str_replace('{{keywords}}', $item['keywords_jp'], $newProductName);
            // dd($newProductName);
            // exit;
            // 基础数据
            $productItem = [
                'name' => $newProductName,
                'english_name' => $item['name'], // 源网站(英)的报告名称作为目标网站的英文报告名称
                'publisher_id' => $defaultPublisherId,
                'category_id' => $item['category_id'],
                'price' => $item['price'],
                'keywords' => $item['keywords_jp'],
                'url' => $item['url'],
                'published_date' => strtotime($item['published_date']),
                'status' => 1,
                'author' => $item['author'],
                'pages' => $item['pages'],
                'tables' => $item['tables'],
                'hits' => $item['hits'] ?? 0,
                'downloads' => $item['downloads'] ?? 0,
                'classification' => $item['classification'],
                'application' => $item['application'],
                'cagr' => $item['cagr'],
                'last_scale' => $item['last_scale'],
                'current_scale' => $item['current_scale'],
                'future_scale' => $item['future_scale'],
                'year' => $item['year'],
                'keywords_cn' => $item['keywords_cn'],
                'keywords_en' => $item['keywords_en'],
                'keywords_jp' => $item['keywords_jp'],
                'keywords_kr' => $item['keywords_kr'],
                'keywords_de' => $item['keywords_de'],
            ];

            $productDescriptionItem = [
                'description_en' => $item['description'],
                'table_of_content_en' => $item['table_of_content'],
                'tables_and_figures_en' => $item['tables_and_figures'],
                'companies_mentioned' => $item['companies_mentioned'],
                'updated_at' => time(),
                'definition' => $item['definition'],
                'overview' => $item['overview'],
            ];
            $product_id = 0;
            $productChange = false; // 报告的类型、应用、企业等数据是否有变化
            if (in_array($item['name'], $existNameArray)) {
                // 修改
                $existProduct = $existData[$item['name']];
                $product_id =  $existProduct['id'];
                $productDescriptionItem['product_id'] = $product_id;
                $productModel = new Products();
                $productModel->where('id', $product_id)->first();

                // 属性是否有变动
                if (
                    $productModel
                    && (
                        $productModel->classification != $item['classification']
                        || $productModel->application != $item['application']
                        || $productModel->cagr != $item['cagr']
                        || $productModel->last_scale != $item['last_scale']
                        || $productModel->current_scale != $item['current_scale']
                        || $productModel->future_scale != $item['future_scale']
                    )
                ) {
                    $productChange = true;
                }

                $productModel->update($productItem);
                //旧纪录年份
                $oldPublishedDate = $existProduct['published_date'];
                $oldYear = Products::publishedDateFormatYear($oldPublishedDate);
                $newYear = Products::publishedDateFormatYear($item['published_date']);
                $newProductDescription = (new ProductsDescription($newYear));
                //出版时间年份更改
                if ($oldYear != $newYear) {
                    //删除旧详情
                    if ($oldYear) {
                        $oldProductDescription = (new ProductsDescription($oldYear))->where('product_id', $product_id)->first();

                        // 属性是否有变动
                        if (
                            $oldProductDescription
                            && $oldProductDescription['companies_mentioned'] == $productDescriptionItem['companies_mentioned']
                        ) {
                            $productChange = true;
                        }
                        if ($oldProductDescription) {
                            $oldProductDescription->delete();
                        }
                    }
                    //然后新增
                    $descriptionRecord = $newProductDescription->saveWithAttributes($productDescriptionItem);
                } else {
                    //直接更新
                    $newProductDescriptionUpdate = $newProductDescription->where('product_id', $product_id)
                        ->first();

                    // 属性是否有变动
                    if (
                        $newProductDescriptionUpdate
                        && $newProductDescriptionUpdate['companies_mentioned'] == $productDescriptionItem['companies_mentioned']
                    ) {
                        $productChange = true;
                    }
                    if ($newProductDescriptionUpdate) {
                        $descriptionRecord = $newProductDescriptionUpdate->updateWithAttributes($productDescriptionItem);
                    } else {
                        $descriptionRecord = $newProductDescription->saveWithAttributes($productDescriptionItem);
                    }
                }
                $updateCount++;
                $insertDetail .= "报告id:{$product_id};【{$item['name']}】" . "\r\n";
                array_push($succIdList, $product_id);
            } else {
                // 新增
                $product = Products::create($productItem);
                $product_id = $product['id'];
                //新增报告详情
                $newYear = Products::publishedDateFormatYear($item['published_date']);
                $newProductDescription = (new ProductsDescription($newYear));
                $productDescriptionItem['product_id'] = $product['id'];
                $descriptionRecord = $newProductDescription->saveWithAttributes($productDescriptionItem);
                $insertCount++;
                $insertDetail .= "报告id:{$product['id']};【{$product['name']}】" . "\r\n";
                array_push($succIdList, $product['id']);
            }



            // 更新或新增课题
            if (!empty($product_id)) {
                try {
                    // 修改课题
                    $postSubject = PostSubject::query()->where('product_id', $product_id)->first();
                    $postSubjectUpdate = [];
                    $postSubjectUpdate['name'] = $newProductName;
                    $postSubjectUpdate['type'] = PostSubject::TYPE_POST_SUBJECT;
                    $postSubjectUpdate['product_id'] = $product_id;
                    $postSubjectUpdate['product_category_id'] = $item['category_id'];
                    $postSubjectUpdate['version'] = intval($item['price'] ?? 0);
                    $postSubjectUpdate['analyst'] = $item['author'];
                    $postSubjectUpdate['keywords'] = $item['keywords_jp'];
                    $postSubjectUpdate['has_cagr'] = !empty($item['cagr']) ? 1 : 0;
                    if ($postSubject) {
                        // 需比对类型、应用、企业是否有变化，有则打开修改状态
                        if ($productChange && !empty($postSubject->propagate_status)) {
                            $postSubjectUpdate['change_status'] = 1;
                        }
                        postSubject::query()->where('product_id', $product_id)->update($postSubjectUpdate);
                    } else {
                        postSubject::create($postSubjectUpdate);
                    }
                } catch (\Throwable $psth) {
                }
            }
        }

        //批量推送sphinx索引
        (new SyncThirdProductController())->PushSphinxQueueByIdList($succIdList, $originSiteName);


        // 修改起始时间
        SystemValue::where('key', 'sync_start_timestamp')->update(['value' => $lastUpdateTime]);
        SystemValue::where('key', 'sync_start_product_id')->update(['value' => $lastProductId]);


        // 记录日志
        $logModel = new SyncLog();
        $logData = [
            'count'         => $count,
            'insert_count'  => $insertCount,
            'update_count'  => $updateCount,
            'ingore_count'  => $ingoreCount,
            'error_count'   => $errorCount,
            'error_detail'  => $details,
            'ingore_detail' => $ingore_detail,
            'update_detail' => $updateDetail,
            'insert_detail' => $insertDetail,
            'created_at'    => $start_time,
            'updated_at'    => time(),
        ];
        $logModel->insert($logData);

        dd('执行结束');
    }

    public function makeSign($data, $signkey)
    {
        unset($data['sign']);
        $signStr = '';
        ksort($data);
        foreach ($data as $key => $value) {
            $signStr .= $key . '=' . $value . '&';
        }
        $signStr .= "key={$signkey}";

        //dump($signStr);
        return md5($signStr);
    }
}
