<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\Database;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\Products;
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

        // 查询设定的起始时间 以及 目标站点
        $startTimestamp = SystemValue::where('key', 'sync_start_timestamp')->value('value');
        $siteName = SystemValue::where('key', 'sync_target_site_name')->value('value');

        // 查询数据库配置
        $targetSite = Site::query()->where('name', $siteName)->first();
        if (!$targetSite) {
            echo '找不到相关站点' . PHP_EOL;
            exit;
        }
        // $database = Database::query()->where('id',$targetSite->database_id)->first();
        // if(!$database){
        //     echo '找不到'.$siteName .'的相关数据库配置'.PHP_EOL;
        //     exit;
        // }

        // 查询目标网站的报告数据 需大于设定的起始时间且有日文关键词
        tenancy()->initialize($targetSite->name);
        $baseQuery = Products::query()
            ->where(function ($query) use ($startTimestamp) {
                $query->where('created_at', '>', $startTimestamp)->orWhere('updated_at', '>', $startTimestamp);
            })
            ->where(function ($query) {

                $query->whereNotNull('keywords_jp')->where('keywords_jp', '<>', '');
            });

        // 一次取1000条数据
        $productData = $baseQuery->limit(1000)->get()->toArray();
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
                $productData[$key] = array_merge($item, $productDescription ?? []);
                $productNameArray[] = $item['name'];
            }
        } else {

            echo '没有数据' . PHP_EOL;
            exit;
        }

        // 切换回本站点更新数据
        tenancy()->initialize($originSiteName);

        //默认出版商
        $publisherIds = Site::where('name', $targetSite->name)->value('publisher_id');
        $publisherIdArray = explode(',', $publisherIds);
        $defaultPublisherId = $publisherIdArray[0];

        // 行业

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
}
