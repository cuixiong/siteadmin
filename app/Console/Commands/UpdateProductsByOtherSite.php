<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Admin\Http\Models\Database;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\SystemValue;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;

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

    /**
     * 日文网站从英文网站获取报告数据
     *
     * @return int
     */
    public function handle()
    {

        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", -1);

        $option = $this->option();
        $originSiteName = $option['site'];
        if (empty($originSiteName)) {
            echo '参数异常' . PHP_EOL;
            exit;
        }

        $startTimestamp = SystemValue::where('key', 'sync_start_timestamp')->value('value');
        $url = SystemValue::where('key', 'sync_target_url')->value('value');

        // $domain = 'https://www.qyresearch.com/';
        // $url = $domain . '/api/third/get-product-data';

        // $url = 'https://www.lpinformationdata.com/test/get-product-data';

        if (empty($url)) {
            echo '缺少地址' . "\n";
        }

        // 查询目标网站的报告数据 需大于设定的起始时间且有日文关键词
        tenancy()->initialize($originSiteName);

        // 请求数据
        $reqData = [
            'startTimestamp'    => $startTimestamp,
            'num'    => 1000,
        ];
        $reqData['sign'] = $this->makeSign($reqData, $this->signKey);
        $response = Http::post($url, $reqData);
        $resp = $response->json();
        if (empty($resp) || $resp['code'] != 200) {
            echo '请求数据失败' . "\n" . json_encode($resp) . "\n";
        }

        $productData = $resp;
        if (empty($productData)) {
            echo '请求无数据' . "\n";
        }

        $productNameArray = [];
        if ($productData) {
            foreach ($productData as $key => $item) {

                $suffix = date('Y', $item['published_date']);
                $productDescription = (new ProductsDescription($suffix))
                    ->select([
                        'description',
                        'table_of_content',
                        'companies_mentioned',
                        'definition',
                        'overview'
                    ])
                    ->where('product_id', $item['id'])
                    ->first();
                $productData[$key] = array_merge($item, $productDescription ? $productDescription->toArray() : []);
                $productNameArray[] = $item['name'];
            }
        } else {

            echo '没有数据' . PHP_EOL;
            exit;
        }

        //默认出版商
        $publisherIds = Site::where('name', $originSiteName)->value('publisher_id');
        $publisherIdArray = explode(',', $publisherIds);
        $defaultPublisherId = $publisherIdArray[0];

        // 行业
        $categoryData = ProductsCategory::query()->where("status", 1)->pluck("id", "link")->toArray();
        $categoryData = array_column($categoryData, 'id', 'link');


        // 默认标题模板
        $templateTitleCache = [];
        $templateCategory = TemplateCategory::query()->select(['id', 'keywords'])->where(['status' => 1])->orderBy(['order' => SORT_ASC, 'id' => SORT_DESC])->get()->toArray();


        // 去重
        $existData = Products::query()->select(['id', 'name', 'published_date'])->whereIn('name', $productNameArray)->get()->toArray();
        $existNameArray = array_column($existData, 'name');
        $existData = array_column($existData, null, 'name');
        foreach ($productData as $key => $item) {
            if (empty($item['name'])) {
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
                if (empty($templateCategoryItem['keywords'])) {
                    if ($defaultTemplateCategory == 0) {
                        $defaultTemplateCategory = $templateCategoryItem['id'];
                    }
                    continue;
                }
                $templateCategorykeywords = explode(',', $templateCategoryItem['keywords']);
                //只需满足任意关键词
                $flag = false;
                foreach ($templateCategorykeywords as $categorykeyword) {
                    if (strpos($item['description_en'], $categorykeyword) !== false) {
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
                $templateTitle = Template::query()->select(['content'])
                    // ->where(['status' => 1])
                    ->where(['type' => 1])
                    ->whereRaw("FIND_IN_SET(?, category_id) > 0", [$defaultTemplateCategory])
                    ->orderBy(['order' => SORT_DESC, 'id' => SORT_DESC])
                    ->asArray()->scalar();

                $templateTitleCache[$defaultTemplateCategory] = $templateTitle;
            }

            $newProductName = $templateTitle;
            $newProductName = str_replace('@@@@', $item['keywords'], $newProductName);
            $newProductName = str_replace('{{keywords}}', $item['keywords'], $newProductName);

            // 基础数据
            $productItem = [
                'name' => $newProductName,
                'english_name' => $item['name'], // 源网站(英)的报告名称作为目标网站的英文报告名称
                'publisher_id' => $defaultPublisherId,
                'category_id' => $item['category_id'],
                'price' => $item['price'],
                'keywords' => $item['keywords'],
                'url' => $item['url'],
                'published_date' => $item['published_date'],
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
            } else {
                // 新增
                $product = Products::create($item);
                $product_id = $product['id'];
                //新增报告详情
                $newYear = Products::publishedDateFormatYear($item['published_date']);
                $newProductDescription = (new ProductsDescription($newYear));
                $productDescriptionItem['product_id'] = $product['id'];
                $descriptionRecord = $newProductDescription->saveWithAttributes($productDescriptionItem);
            }

            // 更新或新增课题
            if (!empty($product_id)) {
                try {
                    // 修改课题
                    $postSubject = PostSubject::query()->where('product_id', $product_id)->first();
                    $postSubjectUpdate = [];
                    $postSubjectUpdate['name'] = $item['name'];
                    $postSubjectUpdate['type'] = PostSubject::TYPE_POST_SUBJECT;
                    $postSubjectUpdate['product_id'] = $product_id;
                    $postSubjectUpdate['product_category_id'] = $item['category_id'];
                    $postSubjectUpdate['version'] = intval($item['price'] ?? 0);
                    $postSubjectUpdate['analyst'] = $item['author'];
                    $postSubjectUpdate['keywords'] = $item['keywords'];
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


        // 修改起始时间

        // $startTimestamp = SystemValue::where('key', 'sync_start_timestamp')->update(['value'=>]);


        dd('完成');
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
