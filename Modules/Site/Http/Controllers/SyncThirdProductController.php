<?php
/**
 * SyncThirdProductController.php UTF-8
 * 同步第三方报告控制器
 *
 * @date    : 2024/6/14 10:00 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Controllers;

use App\Const\QueueConst;
use App\Jobs\SyncSphinxIndex;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\Region;
use Modules\Site\Http\Models\SensitiveWords;
use Modules\Site\Http\Models\SyncField;
use Modules\Site\Http\Models\SyncLog;
use Modules\Site\Http\Models\SyncPublisher;

class SyncThirdProductController extends CrudController {
    public $site            = '';
    public $productCategory = [];
    public $regionList      = [];
    public $senWords        = [];

    /**
     * AJax单行删除
     *
     * @param $ids 主键ID
     */
    protected function destroy(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            foreach ($ids as $id) {
                $modelInstance = new SyncLog();
                $record = $modelInstance->find($id);
                if ($record) {
                    $record->delete();
                }
            }
            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单个查询
     *
     * @param $request 请求信息
     */
    protected function form(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = new SyncLog();
            $record = $ModelInstance->findOrFail($request->id);
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    protected function list(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = new SyncLog();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            // 总数量
            $total = $model->count();
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            if (empty($request->sort)) {
                $sort = 'desc';
            }
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('id', $sort)->orderBy('created_at', 'DESC');
            }
            $record = $model->get();
            $data = [
                'total' => $total,
                'list'  => $record
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function sync(Request $request) {
        try {
            //5s内只能点击一次
            currentLimit($request, 5);
            $respData = $this->pullProductData();
            ReturnJson(true, 'ok', $respData);
        } catch (Exception $e) {
            // 处理异常
            ReturnJson(false, $e->getMessage());
        }
    }

    public function handlerSyncDataJob() {
        try {
            $respData = $this->pullProductData();

            return $respData;
        } catch (\Exception $e) {
            // 处理异常
            throw $e;
        }
    }

    public function handlerRespData($respDataList) {
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
        //默认出版商
        $publisherIds = Site::where('name', $site)->value('publisher_id');
        $publisherIdArray = explode(',', $publisherIds);
        $defaultPublisherId = $publisherIdArray[0];
        $syncFieldList = SyncField::query()->where("status", 1)->get()->keyBy('name')->toArray();
        $count = 0;
        $insertCount = 0;
        $updateCount = 0;
        $errorCount = 0;
        $details = '';
        //移除事件监听
        $dispatcher = Products::getEventDispatcher();
        Products::unsetEventDispatcher();
        //获取敏感词列表
        $this->senWords = SensitiveWords::query()->where("status", 1)
                                        ->pluck("word")->toArray();
        //获取报告分类列表
        $this->productCategory = ProductsCategory::query()->where("status", 1)
                                                 ->pluck("id", "name")->toArray();
        //获取地区列表
        $this->regionList = Region::query()->pluck("id", "name")->toArray();
        //出版商映射关系
        $SyncPublisherList = SyncPublisher::query()->pluck("publisher_id", "third_publisher_code")->toArray();
        // 操作者id
        $user = \Illuminate\Support\Facades\Auth::user();
        if (isset($user->id)) {
            $userID = $user->id;
        } else {
            $userID = 0;
        }
        $errIdList = [];
        $succIdList = [];
        // 从数组中提取出需要排序的列
        $idsSort = array_column($respDataList, 'id');
        // 使用 array_multisort 对原数组进行升序排序
        array_multisort($idsSort, SORT_ASC, $respDataList);
        foreach ($respDataList as &$row) {
            $count++;
            try {
                //字段转换
                foreach ($row as $fieldKey => $fieldVal) {
                    if (!empty($syncFieldList[$fieldKey])) {
                        $realKey = $syncFieldList[$fieldKey]['as_name'];
                        $row[$realKey] = $fieldVal;
                        unset($row[$fieldKey]);
                    }
                }
                // 表头
                $item = [];
                //出版商映射关系
                if (!empty($row['publisher_code'])) {
                    if (!empty($SyncPublisherList[$row['publisher_code']])) {
                        $item['publisher_id'] = $SyncPublisherList[$row['publisher_code']];
                    } else {
                        $item['publisher_id'] = $defaultPublisherId;
                    }
                } else {
                    $item['publisher_id'] = $defaultPublisherId;
                }
                //操作用户
                $item['third_sync_id'] = $row['id'];
                $item['created_by'] = $userID;
                $item['updated_by'] = $userID;
                // 报告名称
                $item['name'] = $row['name'] ?? '';
                //校验报告名称
                $checkMsg = $this->checkProductName($item['name']);
                if ($checkMsg) {
                    $details .= $checkMsg;
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                // 报告名称(英)
                $item['english_name'] = $row['english_name'] ?? '';
                // 页数
                $item['pages'] = $row['pages'] ?? 0;
                // 图表数
                $item['tables'] = $row['tables'] ?? 0;
                // 基础价
                $item['price'] = $row['price'] ?? 0;
                // 忽略基础价为空的数据
                if (empty($item['price'])) {
                    $details .= '【'.($row['name']).'】'.trans('lang.price_empty')."\r\n";
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                // 出版时间
                $item['published_date'] = strtotime($row['published_date']);
                // 忽略出版时间为空或转化失败的数据
                if (empty($item['published_date']) || $item['published_date'] < 0) {
                    $details .= '【'.($row['name'] ?? '').'】'.trans('lang.published_date_empty')."\r\n";
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                // 报告分类
                $tempCategoryId = 0;
                $tempCateName = $row['category_id'] ?? '';
                if (!empty($this->productCategory[trim($tempCateName)])) {
                    $tempCategoryId = $this->productCategory[trim($tempCateName)];
                }
                $item['category_id'] = intval($tempCategoryId);
                // 忽略分类为空的数据
                if (empty($item['category_id'])) {
                    $details .= '【'.($row['name']).'】'.$tempCateName.'-'.trans('lang.category_empty')
                                ."\r\n";
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                //报告所属区域
                $tempCountryId = $row['country_id'] ?? 0;
                if (!empty($tempCountryId) && !empty($this->regionList[trim($tempCountryId)])) {
                    $item['country_id'] = intval($this->regionList[trim($tempCountryId)]);
                } else {
                    $item['country_id'] = 0;
                }
                //作者
                $item['author'] = $row['author'] ?? '';
                //关键词
                $item['keywords'] = $row['keywords'] ?? '';
                // 忽略关键词为空的数据
                if (empty($item['keywords'])) {
                    $details .= '【'.($row['name']).'】'.trans('lang.keywords_empty')."\r\n";
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                // 关键词 含有敏感词的报告需要过滤
                $matchSenWord = $this->checkFitter($item['keywords']);
                if (!empty($matchSenWord)) {
                    $details .= "该报告名称{$item['name']} , 关键词:{$item['keywords']} 含有{$matchSenWord} 敏感词,请检查\r\n";
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                //自定义链接
                $item['url'] = $row['url'] ?? '';
                // 如果链接为空，则用关键词做链接
                if (!empty($row['keywords']) && empty($row['url'])) {
                    $item['url'] = $row['keywords'];
                }
                $item['url'] = strtolower(
                    preg_replace('/%[0-9A-Fa-f]{2}/', '-', urlencode(str_replace(' ', '-', trim($item['url']))))
                );
                $item['url'] = strtolower(
                    preg_replace('/[^A-Za-z0-9-]/', '-', urlencode(str_replace(' ', '-', trim($item['url']))))
                );
                $item['url'] = trim($item['url'], '-'); //左右可能有多余的横杠
                // 忽略url为空的数据
                if (empty($item['url'])) {
                    $details .= '【'.($row['name']).'】'.trans('lang.url_empty')."\r\n";
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                //新增其他扩展字段
                $item['classification'] = $row['classification'] ?? '';
                $item['application'] = $row['application'] ?? '';
                //强校验几个字段
                $last_scale = $row['last_scale'] ?? '';
                if ($this->isDecimalString($last_scale) || is_numeric($last_scale)) {
                    $item['last_scale'] = $last_scale;
                } else {
                    $item['last_scale'] = '';
                }
                $current_scale = $row['current_scale'] ?? '';
                if ($this->isDecimalString($current_scale) || is_numeric($current_scale)) {
                    $item['current_scale'] = $current_scale;
                } else {
                    $item['current_scale'] = '';
                }
                $future_scale = $row['future_scale'] ?? '';
                if ($this->isDecimalString($future_scale) || is_numeric($future_scale)) {
                    $item['future_scale'] = $future_scale;
                } else {
                    $item['future_scale'] = '';
                }
                $cagr = $row['cagr'] ?? '';
                if ($this->isDecimalString($cagr) || is_numeric($cagr)) {
                    $item['cagr'] = $cagr;
                } else {
                    $item['cagr'] = '';
                }
                //详情数据
                $itemDescription = [];
                if (!empty($row['description'])) {
                    $descriptionArr = json_decode($row['description'], true);
                    $row['description'] = $descriptionArr['text'];
                    $itemDescription['description'] = str_replace('_x000D_', '', $row['description']);
                } else {
                    $itemDescription['description'] = '';
                }
                //英文详情
                if (!empty($row['description_en'])) {
                    $descriptionEnArr = json_decode($row['description_en'], true);
                    $row['description_en'] = $descriptionEnArr['text'];
                    $itemDescription['description_en'] = str_replace('_x000D_', '', $row['description_en']);
                } else {
                    $itemDescription['description_en'] = '';
                }
                //正文目录
                if (!empty($row['table_of_content'])) {
                    $tableOfContentArr = json_decode($row['table_of_content'], true);
                    $row['table_of_content'] = $tableOfContentArr['text'];
                    $itemDescription['table_of_content'] = str_replace('_x000D_', '', $row['table_of_content']);
                } else {
                    $itemDescription['table_of_content'] = '';
                }
                //英文正文目录
                if (!empty($row['table_of_content_en'])) {
                    $tableOfContentEnArr = json_decode($row['table_of_content_en'], true);
                    $row['table_of_content_en'] = $tableOfContentEnArr['text'];
                    $itemDescription['table_of_content_en'] = str_replace('_x000D_', '', $row['table_of_content_en']);
                } else {
                    $itemDescription['table_of_content_en'] = '';
                }
                //图表
                if (!empty($row['tables_and_figures'])) {
                    $tablesAndFiguresArr = json_decode($row['tables_and_figures'], true);
                    $row['tables_and_figures'] = $tablesAndFiguresArr['text'];
                    $itemDescription['tables_and_figures'] = str_replace('_x000D_', '', $row['tables_and_figures']);
                } else {
                    $itemDescription['tables_and_figures'] = '';
                }
                //英文图表
                if (!empty($row['tables_and_figures_en'])) {
                    $tablesAndFiguresEn = json_decode($row['tables_and_figures_en'], true);
                    $row['tables_and_figures_en'] = $tablesAndFiguresEn['text'];
                    $itemDescription['tables_and_figures_en'] = str_replace(
                        '_x000D_', '', $row['tables_and_figures_en']
                    );
                } else {
                    $itemDescription['tables_and_figures_en'] = '';
                }
                //提及公司
                if (!empty($row['companies_mentioned'])) {
                    $itemDescription['companies_mentioned'] = str_replace(
                        '_x000D_', '', $row['companies_mentioned']
                    );
                } else {
                    $itemDescription['companies_mentioned'] = '';
                }
                //新增详情字段
                if (!empty($row['definition'])) {
                    $itemDescription['definition'] = str_replace('_x000D_', '', $row['definition']);
                } else {
                    $itemDescription['definition'] = '';
                }
                if (!empty($row['overview'])) {
                    $itemDescription['overview'] = str_replace('_x000D_', '', $row['overview']);
                } else {
                    $itemDescription['overview'] = '';
                }
                $item['year'] = date('Y', $item['published_date']);
                // 查询单个报告数据/去重
                $product = Products::where('name', trim($item['name']))->orWhere(
                    'name', isset($row['english_name']) ? trim(
                    $row['name']
                ) : ''
                )->first();
                // 过滤不符合作者覆盖策略的数据
                if ($product) {
                    if (($product->author == '已售报告' && $item['author'] != '已售报告')
                        || ($product->author == '完成报告'
                            && ($item['author'] != '已售报告'
                                && $item['author'] != '完成报告'))
                    ) {
                        $details .= '【'.($row['name']).'】'.($item['author']).'-'.trans('lang.author_level')
                                    .($product->author)."\r\n";
                        $errorCount++;
                        array_push($errIdList, $row['id']);
                        continue;
                    }
                }
                //测试要求导入报告, 默认 热门 + 精品
                $item['show_hot'] = 1;
                $item['show_recommend'] = 1;
                //新纪录年份
                $newYear = Products::publishedDateFormatYear($item['published_date']);
                /**
                 * 数据库操作
                 */
                if ($product) {
                    $itemDescription['product_id'] = $product->id;
                    //旧纪录年份
                    $oldPublishedDate = $product->published_date;
                    $oldYear = Products::publishedDateFormatYear($oldPublishedDate);
                    //更新报告
                    $product->update($item);
                    $newProductDescription = (new ProductsDescription($newYear));
                    //出版时间年份更改
                    if ($oldYear != $newYear) {
                        //删除旧详情
                        if ($oldYear) {
                            $oldProductDescription = (new ProductsDescription($oldYear))->where(
                                'product_id', $product->id
                            )->first();
                            if ($oldProductDescription) {
                                $oldProductDescription->delete();
                            }
                        }
                        //然后新增
                        $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                    } else {
                        //直接更新
                        $newProductDescriptionUpdate = $newProductDescription->where('product_id', $product->id)->first(
                        );
                        if ($newProductDescriptionUpdate) {
                            $descriptionRecord = $newProductDescriptionUpdate->updateWithAttributes($itemDescription);
                        } else {
                            $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                        }
                    }
                    $updateCount++;
                } else {
                    //新增报告
                    $product = Products::create($item);
                    //新增报告详情
                    $newProductDescription = (new ProductsDescription($newYear));
                    $itemDescription['product_id'] = $product->id;
                    $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                    $insertCount++;
                }
                //执行到这里算是操作成功的
                array_push($succIdList, $row['id']);
                if (!empty($product)) {
                    //维护xunSearch索引, 队列执行
                    $description = $row['description'] ?? '';
                    $this->pushSyncSphinxQueue($product, $description, $site);
                }
            } catch (\Throwable $th) {
                //throw $th;
                $details .= '【'.($row['name']).'】'.$th->getMessage()."\r\n";
                // $details = $th->getLine().$th->getMessage().$th->getTraceAsString() . "\r\n";
                // $details = json_encode($row) . "\r\n";
                $errorCount++;
                array_push($errIdList, $row['id']);
            }
        }
        //恢复监听
        Products::setEventDispatcher($dispatcher);
        // 记录日志
        $logModel = new SyncLog();
        $logData = [
            'count'         => $count,
            'insert_count'  => $insertCount,
            'update_count'  => $updateCount,
            'ingore_count'  => $errorCount,
            'ingore_detail' => $details,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        $logModel->insert($logData);
        $this->notifyThirdRes($succIdList, $errIdList);
    }

    public function notifyThirdRes($sucIdList, $errIdList) {
        //调用接口反馈成功, 失败问题
        $url = "https://hzzb.wanyunapp.com/openapi/v1/updateStatus";
        $token = "6869eec12d49ec06e2cb987e1b20f3585cf196eef3fcebb2c2121f2dd9f4f025";
        $IdsData = [];
        foreach ($sucIdList as $forSucId) {
            $IdsData[] = [
                'f001' => $forSucId
            ];
        }
        $failedIdsData = [];
        foreach ($errIdList as $forErrId) {
            $failedIdsData[] = [
                'f001' => $forErrId
            ];
        }
        $jsonData = [
            'table'      => 'mmg-cn',
            'Ids'        => $IdsData,
            'failed_ids' => $failedIdsData,
        ];
        $jsonStr = json_encode($jsonData);
        try {
            $client = new Client();
            $response = $client->request('POST', $url, [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                    'token'        => $token,
                ],
                RequestOptions::BODY    => $jsonStr,
            ]);
            // 处理响应
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $respData = json_decode($responseBody, true);
            if (!empty($respData) && !empty($respData['errcode']) && $respData['errcode'] == 1) {
                return true;
            } else {
                \Log::error('请求接口失败,请联系管理员:'.json_encode([$url, $token, $respData]));
            }
        } catch (Exception $e) {
            // 处理异常
            throw $e;
        }
    }

    public function checkProductName($productName) {
        // 忽略报告名为空的数据
        if (empty($productName)) {
            return trans('lang.name_empty')."\r\n";
        }
        // 含有敏感词的报告需要过滤
        $matchSenWord = $this->checkFitter($productName);
        if (!empty($matchSenWord)) {
            return "该报告名称{$productName}含有 {$matchSenWord} 敏感词,请检查\r\n";
        }

        return false;
    }

    /**
     *
     * @param array $senWords
     * @param       $name
     *
     */
    private function checkFitter($name) {
        $checkRes = false;
        foreach ($this->senWords as $fillterRules) {
            if (mb_strpos($name, $fillterRules) !== false) {
                $checkRes = $fillterRules;
                break;
            }
        }

        return $checkRes;
    }

    public function isDecimalString($str) {
        // 允许小数点和小数点后的数字
        $pattern = '/^\d+(\.\d+)?%$/';

        return preg_match($pattern, $str) === 1;
    }

    public function pushSyncSphinxQueue($product, $description, $site) {
        $xsProductData = $product->toArray();
        //$xsProductData['description'] = $description ?? '';
        $data = [
            'class'  => 'Modules\Site\Http\Controllers\ProductsUploadLogController',
            'method' => 'xsSyncProductIndex',
            'site'   => $site,
            'data'   => $xsProductData,
        ];
        $data = json_encode($data);
        SyncSphinxIndex::dispatch($data)->onQueue(QueueConst::SYNC_SPGINX_INDEX);
    }

    /**
     *
     *
     * @return mixed
     */
    private function pullProductData() {
        try {
            $url = 'https://hzzb.wanyunapp.com/openapi/v1/mmg_cn';
            $token = '6869eec12d49ec060bb4b333bcc7ff1bdea64cf8e5dc3520f81bdff2fe15f319';
            $jsonData = json_encode([]);
            $client = new Client();
            $response = $client->request('POST', $url, [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                    'token'        => $token,
                ],
                RequestOptions::BODY    => $jsonData,
            ]);
            // 处理响应
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $respData = json_decode($responseBody, true);
            if (!empty($respData) && $statusCode == 200) {
                $respDataList = $respData['data']['data'];
                $this->handlerRespData($respDataList);
                \Log::error('同步完成');
            } else {
                \Log::error('请求接口失败,请联系管理员:'.json_encode([$url, $token, $respData]));
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
