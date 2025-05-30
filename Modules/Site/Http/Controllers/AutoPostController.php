<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Site\Http\Models\AutoPostConfig;
use Modules\Site\Http\Models\AutoPostLog;
use Modules\Site\Http\Models\News;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;
use Modules\Site\Http\Models\TemplateCateMapping;

class AutoPostController extends CrudController
{
    public $site = '';

    public function handlerAutoPostJob()
    {
        try {
            //兼容site
            $site = request()->header('Site');
            if (empty($site) && !empty($this->site)) {
                $site = $this->site;
            }
            if (empty($site)) {
                throw new \Exception("site is empty");

                return false;
            }
            tenancy()->initialize($site);
            //获取所有的自动发帖配置
            $autoPostConfigList = AutoPostConfig::query()->where('status', 1)->get()->toArray();
            foreach ($autoPostConfigList as $autoPostConfig) {
                $this->handService($autoPostConfig, $site);
            }
            ReturnJson(true, trans('lang.request_success'), []);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function handService($autoPostConfig, $site)
    {
        request()->headers->set('Site', $site); // 设置请求头
        //判断参数
        if (empty($autoPostConfig['title_template_ids'] || empty($autoPostConfig['content_template_ids'])
            || empty($autoPostConfig['product_category_ids'] || empty($autoPostConfig['start_product_id'])))) {
            throw new \Exception("param error");
        }

        if ($autoPostConfig['type'] == AutoPostConfig::POST_SITE_TYPE_INSIDE) {
            $this->insideHandle($autoPostConfig);
        } elseif ($autoPostConfig['type'] == AutoPostConfig::POST_SITE_TYPE_OUTSIDE) {
            $this->wpHandle($autoPostConfig);
        }
    }

    // 站内发帖
    private function insideHandle($autoPostConfig)
    {
        $defaultDbConfig = Config::get('database.connections.mysql');
        // 分类报告数据
        // $productCategoryData = ProductsCategory::query()->pluck('name', 'id')->toArray();
        $productCategoryIds = explode(',', $autoPostConfig['product_category_ids']);
        // 定位要发帖的报告
        $productOrginData = Products::query()->select(['id', 'keywords', 'name', 'published_date', 'category_id'])
            ->where('status', 1)
            ->whereIn('category_id', $productCategoryIds)
            ->where('id', '>', $autoPostConfig['start_product_id'])
            ->limit($autoPostConfig['post_num'])
            ->orderBy('id', 'asc')
            ->get()->toArray();

        if (empty($productOrginData)) {
            echo '没有数据' . PHP_EOL;
            return false;
        }
        // 检查是否存在重复记录
        $keywordArray = array_values(array_unique(array_column($productOrginData, 'keywords')));
        // 去重要求 一年内关键词不重复
        $yearTimestamp = strtotime(date('Y-01-01 00:00:00'));
        $existKeywordArray = News::query()->whereIn("keywords", $keywordArray)->where('upload_at', '>=', $yearTimestamp)->pluck('keywords')->toArray();
        $handlerProductList = [];
        $temp = [];
        foreach ($productOrginData as $key => &$item) {
            if (in_array($item['keywords'], $existKeywordArray)) {
                // 数据库去重
                $this->insertAutoPostLog(
                    $autoPostConfig['code'],
                    $item['id'],
                    AutoPostLog::POST_STATUS_EXIST,
                    '数据库已存在'
                );
                continue;
            } elseif (in_array($item['keywords'], $temp)) {

                // 内部去重
                $this->insertAutoPostLog(
                    $autoPostConfig['code'],
                    $item['id'],
                    AutoPostLog::POST_STATUS_EXIST,
                    '同一批数据里重复'
                );
                continue;
            }


            $temp[] = $item['keywords'];
            $handlerProductList[] = $item;
        }
        if (!empty($handlerProductList)) {
            $productArray = array_chunk($handlerProductList, 100);
            foreach ($productArray as $key => $group) {
                $this->insertPost($group, $autoPostConfig, $defaultDbConfig, AutoPostConfig::POST_SITE_TYPE_INSIDE);
            }
            echo '加入队列成功' . PHP_EOL;
        } else {
            echo '过滤重名报告后无数据' . PHP_EOL;
        }
        // 修改起始id
        $lastProductId = end($productOrginData)['id'];
        AutoPostConfig::query()->where('id', $autoPostConfig['id'])
            ->update(['start_product_id' => $lastProductId]);
    }

    private function wpHandle($autoPostConfig)
    {
        $defaultDbConfig = Config::get('database.connections.mysql');
        //wp 数据库连接 (验证数据源)
        $mysql = $this->useRemoteDb($autoPostConfig);
        $wpCateList = DB::connection($mysql)->select(
            "SELECT tt.term_taxonomy_id, t.term_id, t.name FROM wp_terms AS t JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = 'category' "
        );
        $wpCategoryColumn = array_column($wpCateList, 'term_id', 'name');
        //切换回原数据库连接
        $this->uselocalDb($defaultDbConfig);
        // 分类报告数据
        $productCategoryData = ProductsCategory::query()->pluck('name', 'id')->toArray();
        $productCategoryIds = explode(',', $autoPostConfig['product_category_ids']);
        // 定位要发帖的报告
        $productOrginData = Products::query()->select(['id', 'keywords', 'name', 'published_date', 'category_id'])
            ->where('status', 1)
            ->whereIn('category_id', $productCategoryIds)
            ->where('id', '>', $autoPostConfig['start_product_id'])
            ->limit($autoPostConfig['post_num'])
            ->orderBy('id', 'asc')
            ->get()->toArray();
        if (empty($productOrginData)) {
            echo '没有数据' . PHP_EOL;

            return false;
        }
        // 检查是否存在重复记录
        $keywordArray = array_values(array_unique(array_column($productOrginData, 'keywords')));
        $mysql = $this->useRemoteDb($autoPostConfig);
        // 去重要求 一年内关键词不重复
        $year = date('Y', time());
        $existKeywordArray = DB::connection($mysql)->table('wp_posts')
            ->whereIn("post_name", $keywordArray)->where('post_date', '>=', $year)->pluck('post_name')->toArray();
        $this->uselocalDb($defaultDbConfig);
        $handlerProductList = [];
        $temp = [];
        foreach ($productOrginData as $key => &$item) {
            if (in_array($item['keywords'], $existKeywordArray)) {
                // 数据库去重
                $this->insertAutoPostLog(
                    $autoPostConfig['code'],
                    $item['id'],
                    AutoPostLog::POST_STATUS_EXIST,
                    '数据库已存在'
                );
                continue;
            } elseif (in_array($item['keywords'], $temp)) {
                $this->insertAutoPostLog(
                    $autoPostConfig['code'],
                    $item['id'],
                    AutoPostLog::POST_STATUS_EXIST,
                    '内部重复'
                );
                continue;
            }
            $temp[] = $item['keywords'];
            //行业转换
            $item['wp_category_id'] = $wpCategoryColumn[$productCategoryData[$item['category_id']]] ?? 1;
            $handlerProductList[] = $item;
        }
        if (!empty($handlerProductList)) {
            $productArray = array_chunk($handlerProductList, 100);
            foreach ($productArray as $key => $group) {
                $this->insertPost($group, $autoPostConfig, $defaultDbConfig, AutoPostConfig::POST_SITE_TYPE_OUTSIDE);
            }
            echo '加入队列成功' . PHP_EOL;
        } else {
            echo '过滤重名报告后无数据' . PHP_EOL;
        }
        // 修改起始id
        $lastProductId = end($productOrginData)['id'];
        AutoPostConfig::query()->where('id', $autoPostConfig['id'])
            ->update(['start_product_id' => $lastProductId]);
    }

    private function useRemoteDb($autoPostConfig): string
    {
        // 定义新的数据库配置
        $newDatabaseConfig = [
            'driver'    => 'mysql',
            'host'      => $autoPostConfig['db_host'],
            'database'  => $autoPostConfig['db_name'],
            'username'  => $autoPostConfig['db_username'],
            'password'  => $autoPostConfig['db_password'],
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

    private function uselocalDb($dbConfig)
    {
        // 切换到新的数据库配置
        $mysql = "mysql";
        Config::set("database.connections.{$mysql}", $dbConfig);
        // 断开当前连接
        DB::purge($mysql);
        // 重新连接
        DB::reconnect($mysql);

        return $mysql;
    }

    public function insertAutoPostLog(
        $code,
        $product_id,
        $status,
        $detail,
        $wp_link = '',
        $title_template_id = null,
        $content_template_id = null
    ) {
        AutoPostLog::query()->insert([
            'code'                => $code,
            'product_id'          => $product_id,
            'created_at'          => time(),
            'post_status'         => $status,
            'detail'              => $detail,
            'wp_link'             => $wp_link,
            'title_template_id'   => $title_template_id,
            'content_template_id' => $content_template_id,
        ]);
    }

    public function insertPost($productList, $autoPostConf, $defaultDbConfig, $type)
    {
        // 标题模板范围
        $titleTemplateIds = $autoPostConf['title_template_ids'];
        $titleTemplateIds = !empty($titleTemplateIds) ? explode(',', $titleTemplateIds) : [];
        $titleTemplateArray = Template::query()->select(['id', 'name', 'content'])
            ->where('status', 1)
            ->where('type', 2)
            ->whereIn('id', $titleTemplateIds)
            ->get()->toArray();
        // 内容模板范围
        $contentTemplateIds = $autoPostConf['content_template_ids'];
        $contentTemplateIds = !empty($contentTemplateIds) ? explode(',', $contentTemplateIds) : [];
        $contentTemplateArray = Template::query()->select(['id', 'name', 'content'])
            ->where('status', 1)
            ->where('type', 1)
            ->whereIn('id', $contentTemplateIds)
            ->get()->toArray();
        // 模板按分类归类
        $wordCategory = TemplateCategory::query()->select(['id', 'match_words'])->where(['status' => 1])->get()
            ->toArray();
        $newTitleTemplateArray = [];
        foreach ($titleTemplateArray as $key => $item) {
            $item['has_cagr_param'] = strpos($item['content'], '{{cagr}}') !== false;
            $item['has_last_scale_param'] = strpos($item['content'], '{{last_scale}}') !== false;
            $item['has_current_scale_param'] = strpos($item['content'], '{{current_scale}}') !== false;
            $item['has_future_scale_param'] = strpos($item['content'], '{{future_scale}}') !== false;
            $categoryArray = TemplateCateMapping::query()->where('temp_id', $item['id'])->pluck('cate_id')->toArray();
            foreach ($categoryArray as $categoryid) {
                if (!isset($newTitleTemplateArray[$categoryid])) {
                    $newTitleTemplateArray[$categoryid] = [];
                }
                $newTitleTemplateArray[$categoryid][] = $item;
            }
        }
        $newContentTemplateArray = [];
        foreach ($contentTemplateArray as $key => $item) {
            $categoryArray = TemplateCateMapping::query()->where('temp_id', $item['id'])->pluck('cate_id')->toArray();
            foreach ($categoryArray as $categoryid) {
                if (!isset($newContentTemplateArray[$categoryid])) {
                    $newContentTemplateArray[$categoryid] = [];
                }
                $newContentTemplateArray[$categoryid][] = $item;
            }
        }
        // 数据查询
        $data = array_column($productList, null, 'id');
        $productIds = array_keys($data);
        $productData = Products::query()
            // ->select([
            //     'id',
            //     'category_id',
            //     'name',
            //     'keywords',
            //     'url',
            //     'published_date',
            //     'classification',
            //     'application',
            //     'cagr',
            //     'last_scale',
            //     'current_scale',
            //     'future_scale'
            // ])
            ->whereIn('id', $productIds)
            ->get();
        $code = $autoPostConf['code'];
        foreach ($productData as $key => $item) {
            try {
                $item['wp_category_id'] = $data[$item['id']]['wp_category_id'] ?? 0;
                $this->uselocalDb($defaultDbConfig);
                $suffix = date('Y', $item['published_date']);
                $productDescription = (new ProductsDescription($suffix))->where('product_id', $item['id'])
                    // ->select([
                    //     'description',
                    //     'table_of_content',
                    //     'companies_mentioned',
                    //     'definition',
                    //     'overview'
                    // ])
                    ->first();
                if (empty($item) || empty($productDescription)) {
                    $this->insertAutoPostLog($code, $item['id'], AutoPostLog::POST_STATUS_INGORE, '缺少详情数据');
                    continue;
                }
                // 兼容部分日文网站，由于详情为空需要用到英文详情判断使用那个模板，因此这里将description_en的值赋给description
                if(isset($productDescription->description_en) && empty($productDescription->description)){
                    $productDescription->description = $productDescription->description_en;
                }
                $productArr = json_decode(json_encode($productDescription), true) ?? [];
                $itemArr = json_decode(json_encode($item), true) ?? [];
                $product = array_merge($itemArr, $productArr);
                $templateCategoryId = $this->getTemplateCategoryId($wordCategory, $product['description']);
                $product['titleTemplate'] = [];
                if (
                    !isset($newTitleTemplateArray[$templateCategoryId])
                    || count(
                        $newTitleTemplateArray[$templateCategoryId]
                    ) == 0
                ) {
                    $this->insertAutoPostLog($code, $item['id'], AutoPostLog::POST_STATUS_INGORE, '未发现可用标题模板');
                    continue;
                }
                $tempTitleNum = mt_rand(1, count($newTitleTemplateArray[$templateCategoryId]));
                $newTitleTemplate = $newTitleTemplateArray[$templateCategoryId][$tempTitleNum - 1];
                $product['titleTemplate']['id'] = $newTitleTemplate['id'];
                $product['titleTemplate']['content'] = $newTitleTemplate['content'];
                if ($newTitleTemplate['has_cagr_param'] && empty($product['cagr'])) {
                    $this->insertAutoPostLog($code, $item['id'], AutoPostLog::POST_STATUS_INGORE, '{{cagr}}无数据');
                    continue;
                }
                if ($newTitleTemplate['has_last_scale_param'] && empty($product['last_scale'])) {
                    $this->insertAutoPostLog(
                        $code,
                        $item['id'],
                        AutoPostLog::POST_STATUS_INGORE,
                        '{{last_year}}无数据'
                    );
                    continue;
                }
                if ($newTitleTemplate['has_current_scale_param'] && empty($product['current_scale'])) {
                    $this->insertAutoPostLog(
                        $code,
                        $item['id'],
                        AutoPostLog::POST_STATUS_INGORE,
                        '{{this_year}}无数据'
                    );
                    continue;
                }
                if ($newTitleTemplate['has_future_scale_param'] && empty($product['future_scale'])) {
                    $this->insertAutoPostLog($code, $item['id'], AutoPostLog::POST_STATUS_INGORE, '{{six_year}}无数据');
                    continue;
                }
                $product['contentTemplate'] = [];
                if (
                    !isset($newContentTemplateArray[$templateCategoryId])
                    || count(
                        $newContentTemplateArray[$templateCategoryId]
                    ) == 0
                ) {
                    $this->insertAutoPostLog($code, $item['id'], AutoPostLog::POST_STATUS_INGORE, '未发现可用报告模板');
                    continue;
                }
                $tempContentNum = mt_rand(1, count($newContentTemplateArray[$templateCategoryId]));
                $product['contentTemplate']['id'] = $newContentTemplateArray[$templateCategoryId][$tempContentNum
                    - 1]['id'];
                $product['contentTemplate']['content'] = $newContentTemplateArray[$templateCategoryId][$tempContentNum
                    - 1]['content'];
                //计算获取文章信息
                //                $articleInfo = $this->articleInfo(
                //                    $product['titleTemplate']['content'], $product['contentTemplate']['content'], $product
                //                );
                $articleInfo = $this->newarticleInfo(
                    $product['titleTemplate'],
                    $product['contentTemplate'],
                    $item,
                    $productDescription
                );
                $timestamp = time();
                $time = date('Y-m-d H:i:s', $timestamp);
                $articleKeyword = $articleInfo['keyword'];
                // $productName = $item['name'];
                $articleTitle = $articleInfo['title'];
                $articleContent = $articleInfo['content'];
                $articleDescription = $articleInfo['description'];
                if ($type == AutoPostConfig::POST_SITE_TYPE_INSIDE) {
                    $newsModel = new News();
                    $newsModel->title = $articleTitle;
                    $newsModel->category_id = $product['category_id'];
                    $newsModel->type = $autoPostConf['news_category_id'];
                    $newsModel->keywords = $articleKeyword;
                    $newsModel->tags = $articleKeyword;
                    $newsModel->url = $product['url'];
                    $newsModel->description = $articleDescription;
                    $newsModel->content = $articleContent;
                    $newsModel->sort = 100;
                    $newsModel->show_home = 1;
                    $newsModel->status = 1;
                    $newsModel->created_by = 0;
                    $newsModel->created_at = time();
                    $newsModel->updated_by = 0;
                    $newsModel->updated_at = time();
                    $newsModel->upload_at = time();
                    $newsModel->upload_at = time();
                    $newsModel->hits = mt_rand(100, 500);
                    $newsModel->real_hits = 0;
                    $newsModel->save();
                    
                    $domain = $autoPostConf['domain'];
                    $this->insertAutoPostLog(
                        $code,
                        $item['id'],
                        AutoPostLog::POST_STATUS_SUCCESS,
                        '成功',
                        $domain.'/news/'.($newsModel->id) . '/' . $product['url'],
                        $product['titleTemplate']['id'],
                        $product['contentTemplate']['id']
                    );
                } elseif ($type == AutoPostConfig::POST_SITE_TYPE_OUTSIDE) {

                    $author = 1;
                    // 要插入的数据
                    $insertPostData = [
                        'post_excerpt'          => '',
                        'post_status'           => 'publish',
                        'comment_status'        => 'open',
                        'ping_status'           => 'open',
                        'to_ping'               => '',
                        'pinged'                => '',
                        'post_content_filtered' => '',
                        'post_type'             => 'post',
                        'post_author'           => $author,
                        'post_date'             => $time,
                        'post_date_gmt'         => $time,
                        'post_content'          => $articleContent,
                        'post_title'            => $articleTitle,
                        'post_name'             => $articleKeyword,
                        'post_modified'         => $time,
                        'post_modified_gmt'     => $time,
                    ];
                    $insertPostmetaData = [
                        // 一些附带的数据，不确定影不影响
                        // '_yoast_indexnow_last_ping' => $timestamp,
                        // '_yoast_wpseo_primary_category' => '',
                        // '_yoast_wpseo_content_score'    => 30,
                        // '_yoast_wpseo_focuskeywords'    => '',
                        // '_yoast_wpseo_keywordsynonyms'    => '',
                        // '_yoast_wpseo_estimated-reading-time-minutes'    => 0,
                        '_yoast_wpseo_metadesc' => $articleDescription //seo description
                    ];
                    $insertYoastIndexableData = [
                        // 默认数据
                        'object_type'                    => 'post',
                        'object_sub_type'                => 'post',
                        'breadcrumb_title'               => $articleTitle,
                        'post_status'                    => 'post',
                        'readability_score'              => 30,
                        'created_at'                     => $time,
                        'updated_at'                     => $time,
                        'blog_id'                        => 1,
                        'estimated_reading_time_minutes' => 0,
                        'version'                        => 2,
                        'object_last_modified'           => $time,
                        'object_published_at'            => $time,
                        'inclusive_language_score'       => 0,
                        // 重要的数据
                        'permalink'                      => '', // 帖子访问链接
                        'permalink_hash'                 => '', //加密permalink
                        'object_id'                      => 0, //post_id 需插入posts后获取
                        'author_id'                      => $author,
                        'title'                          => $articleTitle . ' - QY Research',
                        'description'                    => $articleDescription,
                    ];
                    $insertTermRelationshipsData = [
                        'object_id'        => 0,
                        'term_taxonomy_id' => $product['wp_category_id'],
                        'term_order'       => 0,
                    ];
                    // wp_post 文章表
                    // wp_postmeta 有个seo description要写入, 点文章编辑的时候会在下方yoast插件显示出来，只有点更新才会生效
                    // wp_yoast_indexable 插件的一个表，wp页面生成时会读取该数据进行生成 ,相关页面缓存在 {wp路径}/wp-content/cache/supercache/{站点}/archives/{id} 中
                    //
                    $mysql = $this->useRemoteDb($autoPostConf);
                    $postId = DB::connection($mysql)->table('wp_posts')->insertGetId($insertPostData);
                    foreach ($insertPostmetaData as $key => $value) {
                        $tempData = [];
                        $tempData['post_id'] = $postId;
                        $tempData['meta_key'] = $key;
                        $tempData['meta_value'] = $value;
                        DB::connection($mysql)->table('wp_postmeta')->insertGetId($tempData);
                    }
                    $domain = $autoPostConf['domain'];
                    $insertYoastIndexableData['object_id'] = $postId;
                    $insertYoastIndexableData['permalink'] = $domain . "/archives/" . $postId;
                    // $this->logger(json_encode(strlen($insertYoastIndexableData['permalink'])));
                    // $this->logger(json_encode(md5($insertYoastIndexableData['permalink'])));
                    // return ;
                    $insertYoastIndexableData['permalink_hash'] = strlen($insertYoastIndexableData['permalink']) . ':' . md5(
                        $insertYoastIndexableData['permalink']
                    );
                    $indexableId = DB::connection($mysql)->table('wp_yoast_indexable')->insertGetId(
                        $insertYoastIndexableData
                    );
                    $insertTermRelationshipsData['object_id'] = $postId;
                    $term_taxonomy_id = DB::connection($mysql)->table('wp_term_relationships')->insertGetId(
                        $insertTermRelationshipsData
                    );
                    $this->insertAutoPostLog(
                        $code,
                        $item['id'],
                        AutoPostLog::POST_STATUS_SUCCESS,
                        '成功',
                        $insertYoastIndexableData['permalink'],
                        $product['titleTemplate']['id'],
                        $product['contentTemplate']['id']
                    );
                }
            } catch (\Exception $e) {
                $this->insertAutoPostLog($code, $item['id'], AutoPostLog::POST_STATUS_ERROR, $e->getMessage());
            }
        }
    }

    private function getTemplateCategoryId($wordCategory, $des)
    {
        $defaultWordCategory = 0;
        foreach ($wordCategory as $key2 => $item2) {
            if (empty($item2['match_words'])) {
                if ($defaultWordCategory == 0) {
                    $defaultWordCategory = $item2['id'];
                }
                continue;
            }
            $wordCategorykeywords = explode(',', $item2['match_words']);
            //只需满足任意关键词
            $flag = false;
            foreach ($wordCategorykeywords as $key3 => $item3) {
                if (strpos($des, $item3) !== false) {
                    $flag = true;
                    break;
                }
            }
            if ($flag) {
                $defaultWordCategory = $item2['id'];
                break;
            }
        }

        return $defaultWordCategory;
    }

    private function newarticleInfo($titleTemplate, $contentTemplate, $product, $productDesc)
    {
        $temolateController = new TemplateController();
        $keyword = $product['keywords'];
        $title = $temolateController->templateWirteData($titleTemplate, $product, $productDesc, true);
        $content = $temolateController->templateWirteData($contentTemplate, $product, $productDesc, true);
        //描述第一段
        $description = $productDesc->description;
        $reg = "/<\/?[a-z]+( [^>]*)?>/";
        $description = preg_replace($reg, "", $description);
        $descriptionSpilt = explode("\n", $description);
        $seo_description = $descriptionSpilt ? ($descriptionSpilt[0] ?? '') : '';
        $content = str_replace('{{seo_description}}', $seo_description, $content);

        return [
            'keyword'     => $keyword,
            'title'       => $title,
            'content'     => $content,
            'description' => $seo_description,
        ];
    }

    // 返回发帖标题、内容等信息
    private function articleInfo($titleTemplate, $contentTemplate, $product)
    {
        // 替换后的标题
        $title = $titleTemplate;
        $title = str_replace('@@@@', $product['keywords'], $title);
        $title = str_replace('{{keywords}}', $product['keywords'], $title);
        $cagr = $product['cagr'] && is_numeric($product['cagr']) ? bcmul($product['cagr'], 100, 2) : '';
        $title = str_replace('{{cagr}}', $cagr, $title);
        $title = str_replace('{{last_year}}', $product['last_scale'] ?? '', $title);
        $title = str_replace('{{this_year}}', $product['current_scale'] ?? '', $title);
        $title = str_replace('{{six_year}}', $product['future_scale'] ?? '', $title);
        // 处理报告数据
        $description = str_replace("\r\n", "\n", $product['description']);
        $toc = $product['table_of_content'];
        $toc = str_replace("\r\n", "\n", $toc);
        //$toc = $this->tocHandle($toc);
        $definition = $product['definition'];
        $overview = $product['overview'];
        // 类型
        $classification = $product['classification'];
        $classification_str = $this->applicationHandle($classification, '、');
        $classification_linefeed = $this->applicationHandle($classification, "<br />", '    ');
        // 应用
        $application = $product['application'];
        $application_str = $this->applicationHandle($application, '、');
        $application_linefeed = $this->applicationHandle($application, "<br />", '    ');
        // 企业
        $company = $product['companies_mentioned'];
        $company_str = $this->applicationHandle($company, '、');
        $company_linefeed = $this->applicationHandle($company, "<br />", '    ');
        $time = time();
        $keyword = $product['keywords'];
        $link = $product['keywords'];
        $id = $product['id'];
        $content = str_replace('@@@@', $keyword, $contentTemplate);
        $content = str_replace('{{keywords}}', $keyword, $content);
        $content = str_replace('{{year}}', date('Y', $time), $content);
        $content = str_replace('{{month}}', date('n', $time), $content);
        $content = str_replace('{{day}}', date('j', $time), $content);
        $content = str_replace('{{id}}', $id, $content);
        $content = str_replace('{{type_str}}', $classification_str, $content);
        $content = str_replace('{{application_str}}', $application_str, $content);
        $content = str_replace('{{company_str}}', $company_str, $content);
        $content = str_replace('{{type}}', $classification_linefeed, $content);
        $content = str_replace('{{application}}', $application_linefeed, $content);
        $content = str_replace('{{company}}', $company_linefeed, $content);
        $content = str_replace('{{toc}}', $toc, $content);
        $content = str_replace('{{definition}}', $definition, $content);
        $content = str_replace('{{overview}}', $overview, $content);
        $content = str_replace('{{cagr}}', $cagr, $content);
        $content = str_replace('{{last_year}}', $product['last_scale'] ?? '', $content);
        $content = str_replace('{{this_year}}', $product['current_scale'] ?? '', $content);
        $content = str_replace('{{six_year}}', $product['future_scale'] ?? '', $content);
        $frontend_domain = getSiteDomain();
        $link = $frontend_domain . '/reports/' . $product['id'] . '/' . $product['url'];
        $linkElement = '<a href="' . $link . '"  title="' . $keyword . '" target="blank">' . $link . '</a>';
        $content = str_replace('{{link}}', $linkElement, $content);
        //描述第一段
        $reg = "/<\/?[a-z]+( [^>]*)?>/";
        $description = preg_replace($reg, "", $description);
        $descriptionSpilt = explode("\n", $description);
        $seo_description = $descriptionSpilt ? ($descriptionSpilt[0] ?? '') : '';
        $content = str_replace('{{seo_description}}', $seo_description, $content);

        return [
            'keyword'     => $keyword,
            'title'       => $title,
            'content'     => $content,
            'description' => $seo_description,
        ];
    }

    /**
     * 对截取的类型、应用进行特殊字符过滤,并且进行缩进、换行等各种格式处理
     */
    public function applicationHandle($text = '', $separator = "", $space = '', $leftTag = '', $rightTag = '')
    {
        if (empty($text)) {
            return '';
        }
        $text = explode(
            "\n",
            str_replace("", '', str_replace("\t", '', trim(str_replace("\r\n", "\n", $text), "\n")))
        );
        //过滤空字符串
        $text = array_filter($text, function ($value) {
            return $value !== "";
        });
        //添加缩进
        $text = array_map(function ($item) use ($space, $leftTag, $rightTag) {
            return $leftTag . $space . trim($item) . $rightTag;
        }, $text);
        //合并
        $text = implode($separator, $text);

        return $text;
    }

    // 对目录进行换行，缩进
    public static function tocHandle($toc)
    {
        $pattern = '/ {0,}(?<!\.)\d{1,2}(\.\d{1,2}){0,3} .{0,}\n/';
        $result = [];
        $match = [];
        try {
            preg_match_all($pattern, $toc, $match);
        } catch (\Throwable $th) {
            //throw $th;
        }
        $numPattern = '/ {0,}(?<!\.)\d{1,2}(\.\d{1,2}){0,3} /';
        if (is_array($match) && count($match) > 0 && count($match[0]) > 0) {
            $count = 0;
            foreach ($match[0] as $key => $value) {
                try {
                    preg_match_all($numPattern, $value, $numMatch);
                } catch (\Throwable $th) {
                    //throw $th;
                }
                $num = str_replace(' ', '', $numMatch[0][0]);
                $value = trim($value, "\r\n");
                $value = trim($value, "\r");
                $value = trim($value, " ");
                if (!empty($value) && strpos($num, ".") === false) {
                    $count = $count + 1;
                    preg_match('/(?<!.)\d{1,2} /', trim($value, "\n"), $matchTitle);
                    $value = trim($value, "\r\n");
                    $value = trim($value, "\n");
                    $result[$count] .= trim($value, "\n") . '<br />';
                } else {
                    if (!isset($result[$count])) {
                        continue;
                    }
                    $value = trim($value, "\r\n");
                    $value = trim($value, "\n");
                    $space = '';
                    $str_count = substr_count($num, '.') ?? 0;
                    for ($i = 0; $i < $str_count; $i++) {
                        $space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    $result[$count] .= $space . trim(str_replace("\n", "<br />", $value), "\n") . "<br />";
                }
            }
        }

        return implode("", $result);
    }
}
