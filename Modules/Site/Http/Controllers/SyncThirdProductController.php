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
use Illuminate\Support\Facades\App;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\Region;
use Modules\Site\Http\Models\SensitiveWords;
use Modules\Site\Http\Models\SyncField;
use Modules\Site\Http\Models\SyncLog;
use Modules\Site\Http\Models\SyncPublisher;
use Modules\Site\Http\Models\SystemValue;

class SyncThirdProductController extends CrudController {
    public        $site                   = '';
    public        $productCategory        = [];
    public        $regionList             = [];
    public        $senWords               = [];
    public        $autoSyncDataKey        = 'autoSyncData';
    public        $syncProductUrlKey      = 'syncProductUrl';
    public        $syncProductTokenKey    = 'syncProductToken';
    public        $notifyDataResUrlKey    = 'notifyDataResUrl';
    public        $notifyDataResTableKey  = 'notifyResTable';
    public        $notifyDataSyncTokenKey = 'notifyDataSyncToken';
    public static $openAutoSyncData       = 1;
    public static $closeAutoSyncData      = 0;
    public        $syncConfig             = [];

    public function searchDroplist(Request $request) {
        try {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field,
                false,
                '',
                ['code' => 'Switch_State', 'status' => 1],
                ['sort' => 'ASC']
            );
            // 自动拉取数据开关
            $value = SystemValue::query()->where("key", $this->autoSyncDataKey)->value('value');
            $data['auto_sync_data'] = $value;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function changeAutoSyncStatus(Request $request) {
        try {
            if (!isset($request->status)) {
                ReturnJson(false, '状态异常');
            }
            $info = SystemValue::query()->where("key", $this->autoSyncDataKey)->first();
            if (empty($info)) {
                ReturnJson(false, '数据不存在');
            }
            if ($request->status != 1) {
                $value = self::$closeAutoSyncData;
            } else {
                $value = self::$openAutoSyncData;
            }
            $info->value = $value;
            $rs = $info->save();
            if ($rs > 0) {
                ReturnJson(true, trans('lang.request_success'), []);
            } else {
                ReturnJson(false, trans('lang.update_error'));
            }
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

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
            try {
                $respData = $this->pullProductData();
                ReturnJson(true, 'ok', $respData);
            } catch (\Exception $e) {
                ReturnJson(false, $e->getMessage());
            }
        } catch (Exception $e) {
            // 处理异常
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 定时任务执行方法
     *
     * @return mixed|true
     */
    public function handlerSyncDataJob() {
        try {
            $respData = $this->pullProductData(true);

            return $respData;
        } catch (\Exception $e) {
            // 处理异常
            throw $e;
        }
    }

    public function newHandlerRespData($respDataList, $site) {
        if (empty($respDataList)) {
            \Log::error('拉取北京数据本次为空');
            throw new \Exception('本次拉取数据为空');

            return false;
        }
        //默认出版商
        $publisherIds = Site::where('name', $site)->value('publisher_id');
        $publisherIdArray = explode(',', $publisherIds);
        $defaultPublisherId = $publisherIdArray[0];
        $syncFieldList = SyncField::query()->where("status", 1)->get()->toArray();
        $count = 0;
        $start_time = time();
        $insertCount = 0;
        $updateCount = 0;
        $errorCount = 0;
        $ingoreCount = 0;
        $details = '';
        $updateDetail = '';
        $insertDetail = '';
        $ingore_detail = '';
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
        $thirdIdList = [];
        // 从数组中提取出需要排序的列
        $idsSort = array_column($respDataList, 'id');
        // 使用 array_multisort 对原数组进行升序排序
        array_multisort($idsSort, SORT_ASC, $respDataList);

        // 字段转换，要求北京的字段可以重复利用
        $productData = [];
        foreach ($respDataList as $respData) {
            $temp = [];
            foreach ($syncFieldList as $syncFieldItem) {
                
                if (isset($respData[$syncFieldItem['name']])) {
                    $temp[$syncFieldItem['as_name']] = $respData[$syncFieldItem['name']]??'';
                }
            }
            $temp['id'] = $respData['id'];// 此id是北京数据的id
            $productData[] = $temp;
        }
        // 
        // foreach ($respDataList as &$respData) {
        //     //字段转换
        //     foreach ($respData as $fieldKey => $fieldVal) {
        //         if (!empty($syncFieldList[$fieldKey])) {
        //             $realKey = $syncFieldList[$fieldKey]['as_name'];
        //             $respData[$realKey] = $fieldVal;
        //             unset($respData[$fieldKey]);
        //         }
        //     }
        // }


        //昵称去重
        $uniqueDataList = [];
        $authorCheck = ['已售报告', '完成报告'];
        foreach ($productData as $forParamsData) {
            $count++;
            
            //校验报告名称
            $checkMsg = $this->checkProductName($forParamsData['name']);
            if ($checkMsg) {
                $details .= "【错误】编号:{$forParamsData['id']}:  ".$checkMsg;
                $errorCount++;
                array_push($errIdList, $forParamsData['id']);
                continue;
            }
            
            //已售报告>完成报告>人名作者
            if (!empty($uniqueDataList[$forParamsData['name']])) {
                if (
                    !in_array($uniqueDataList[$forParamsData['name']]['author'], $authorCheck)
                    && in_array($forParamsData['author'], $authorCheck)
                ) {
                    $ingore_detail .= "【错误】编号:{$uniqueDataList[$forParamsData['name']]['id']};【{$forParamsData['name']}】"
                                      .($uniqueDataList[$forParamsData['name']]['author']).'-'.trans(
                                          'lang.author_level'
                                      )
                                      .($forParamsData['author'])."\r\n";
                    $ingoreCount++;
                    array_push($errIdList, $uniqueDataList[$forParamsData['name']]['id']);
                    $uniqueDataList[$forParamsData['name']] = $forParamsData;
                } elseif (
                    in_array($uniqueDataList[$forParamsData['name']]['author'], $authorCheck)
                    && $forParamsData['author'] == '已售报告'
                ) {
                    $ingore_detail .= "【错误】编号:{$uniqueDataList[$forParamsData['name']]['id']};【{$forParamsData['name']}】"
                                      .($uniqueDataList[$forParamsData['name']]['author']).'-'.trans(
                                          'lang.author_level'
                                      )
                                      .($forParamsData['author'])."\r\n";
                    $ingoreCount++;
                    array_push($errIdList, $uniqueDataList[$forParamsData['name']]['id']);
                    $uniqueDataList[$forParamsData['name']] = $forParamsData;
                } elseif ($uniqueDataList[$forParamsData['name']]['author'] == $forParamsData['author']) {
                    $ingore_detail .= "【错误】编号:{$uniqueDataList[$forParamsData['name']]['id']};【{$forParamsData['name']}】"
                                      .($uniqueDataList[$forParamsData['name']]['author']).'-'.trans(
                                          'lang.author_level'
                                      )
                                      .($forParamsData['author'])."\r\n";
                    $ingoreCount++;
                    array_push($errIdList, $uniqueDataList[$forParamsData['name']]['id']);
                    $uniqueDataList[$forParamsData['name']] = $forParamsData;
                }
            } else {
                $uniqueDataList[$forParamsData['name']] = $forParamsData;
            }
        }
        $uniqueDataList = array_values($uniqueDataList);
        try {
            $pro_name_list = array_column($uniqueDataList, 'name');
            $pro_ename_list = array_column($uniqueDataList, 'english_name');
            $handler_name_list = array_merge($pro_name_list, $pro_ename_list);
            $handler_after_name_list = [];
            if (!empty($handler_name_list)) {
                $handler_name_list = array_unique($handler_name_list);
                foreach ($handler_name_list as $forName) {
                    if (!empty($forName)) {
                        $handler_after_name_list[] = $forName;
                    }
                }
            }
            if (!empty($handler_after_name_list)) {
                $productList = Products::whereIn('name', $handler_after_name_list)->select(
                    ['id', 'name', 'author', 'published_date']
                )->get()->keyBy('name')->toArray();
            } else {
                $productList = [];
            }
            $product_model = new Products();
            foreach ($uniqueDataList as &$row) {
                $productChange = false; // 报告的类型、应用、企业等数据是否有变化
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
                //报告名称
                $product = [];
                if (!empty($productList[$row['name']])) {
                    $product = $productList[$row['name']];
                    if (($product['author'] == '已售报告' && $row['author'] != '已售报告')
                        || ($product['author'] == '完成报告'
                            && ($row['author'] != '已售报告'
                                && $row['author'] != '完成报告'))
                    ) {
                        $ingore_detail .= "【错误】编号:{$item['third_sync_id']};报告id:{$product['id']};【{$row['name']}】"
                            . ($row['author']) . '-' . trans('lang.author_level')
                            . ($product['author']) . "\r\n";
                        $ingoreCount++;
                        array_push($errIdList, $row['id']);
                        continue;
                    }
                }
                // 报告名称
                $item['name'] = $row['name'] ?? '';
                //校验报告名称
                $checkMsg = $this->checkProductName($item['name']);
                if ($checkMsg) {
                    $details .= "【错误】编号:{$item['third_sync_id']}:  " . $checkMsg;
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                // 报告名称(英)
                $item['english_name'] = $row['english_name'] ?? '';
                // 英文昵称含有敏感词的报告需要过滤
                //                $matchSenWord = $this->checkFitter($item['english_name']);
                //                if (!empty($matchSenWord)) {
                //                    $details .= "【错误】编号:{$item['third_sync_id']} : 该英文报告名称{$item['english_name']}含有 {$matchSenWord} 敏感词,请检查\r\n";
                //                    $errorCount++;
                //                    array_push($errIdList, $row['id']);
                //                    continue;
                //                }
                // 页数
                $item['pages'] = $row['pages'] ?? 0;
                // 图表数
                $item['tables'] = $row['tables'] ?? 0;
                // 基础价
                $item['price'] = $row['price'] ?? 0;
                // 忽略基础价为空的数据
                if (empty($item['price'])) {
                    $details .= "【错误】编号:{$item['third_sync_id']}:   " . '【' . ($row['name']) . '】' . trans(
                        'lang.price_empty'
                    ) . "\r\n";
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                // 出版时间
                $item['published_date'] = strtotime($row['published_date']);
                // 忽略出版时间为空或转化失败的数据
                if (empty($item['published_date']) || $item['published_date'] < 0) {
                    $details .= "【错误】编号:{$item['third_sync_id']}:   " . '【' . ($row['name'] ?? '') . '】' . trans(
                        'lang.published_date_empty'
                    ) . "\r\n";
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
                    $details .= "【错误】编号:{$item['third_sync_id']}:   " . '【' . ($row['name']) . '】' . $tempCateName . '-'
                        . trans('lang.category_empty')
                        . "\r\n";
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
                    $details .= "【错误】编号:{$item['third_sync_id']}:   " . '【' . ($row['name']) . '】' . trans(
                        'lang.keywords_empty'
                    ) . "\r\n";
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
                    $details .= "【错误】编号:{$item['third_sync_id']}:   " . '【' . ($row['name']) . '】' . trans('lang.url_empty')
                        . "\r\n";
                    $errorCount++;
                    array_push($errIdList, $row['id']);
                    continue;
                }
                //url链接也需要检测敏感词
                $matchSenWord = $this->checkFitter($item['url']);
                if (!empty($matchSenWord)) {
                    $details .= "【错误】编号:{$item['third_sync_id']}:   "
                        . "该报告名称{$item['name']} , url: {$item['url']} ,含有 {$matchSenWord} 敏感词,请检查\r\n";
                    array_push($errIdList, $row['id']);
                    $errorCount++;
                    continue;
                }

                $item['keywords_cn'] = $row['keywords_cn'] ?? '';
                $item['keywords_en'] = $row['keywords_en'] ?? '';
                $item['keywords_jp'] = isset($row['keywords_jp']) && $row['keywords_jp'] != $item['keywords_en'] ? $row['keywords_jp'] : '';
                $item['keywords_kr'] = isset($row['keywords_kr']) && $row['keywords_kr'] != $item['keywords_en'] ? $row['keywords_kr'] : '';
                $item['keywords_de'] = isset($row['keywords_de']) && $row['keywords_de'] != $item['keywords_en'] ? $row['keywords_de'] : '';

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
                        '_x000D_',
                        '',
                        $row['tables_and_figures_en']
                    );
                } else {
                    $itemDescription['tables_and_figures_en'] = '';
                }
                //提及公司
                if (!empty($row['companies_mentioned'])) {
                    $itemDescription['companies_mentioned'] = str_replace(
                        '_x000D_',
                        '',
                        $row['companies_mentioned']
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
                //测试要求导入报告, 默认 热门 + 精品
                //                $item['show_hot'] = 1;
                //                $item['show_recommend'] = 1;
                //新纪录年份
                $newYear = Products::publishedDateFormatYear($item['published_date']);
                /**
                 * 数据库操作
                 */
                if (!empty($product)) {
                    //新增字段 初始化一个浏览次数和下载次数,存在则不修改
                    $forProductId = $product['id'];
                    $item['hits'] = mt_rand(100, 500);
                    $item['downloads'] = mt_rand(10, 99);
                    $itemDescription['product_id'] = $product['id'];
                    //旧纪录年份
                    $oldPublishedDate = $product['published_date'];
                    $oldYear = Products::publishedDateFormatYear($oldPublishedDate);
                    $product_model = $product_model->where("id", $product['id'])->first();
                    // $originalAttributes = $product_model->getAttributes();
                    // 属性是否有变动
                    if (
                        $product_model
                        && (
                            $product_model->classification != $row['classification']
                            || $product_model->application != $row['application']
                            || $product_model->cagr != $row['cagr']
                            || $product_model->last_scale != $row['last_scale']
                            || $product_model->current_scale != $row['current_scale']
                            || $product_model->future_scale != $row['future_scale']
                        )
                    ) {
                        $productChange = true;
                    }
                    //更新报告
                    $product_model->update($item);
                    // $changedAttributes = $product_model->getDirty();
                    $newProductDescription = (new ProductsDescription($newYear));
                    //出版时间年份更改
                    if ($oldYear != $newYear) {
                        //删除旧详情
                        if ($oldYear) {
                            $oldProductDescription = (new ProductsDescription($oldYear))->where(
                                'product_id',
                                $product['id']
                            )->first();
                            // 属性是否有变动
                            if (
                                $oldProductDescription
                                && $oldProductDescription['companies_mentioned']
                                == $itemDescription['companies_mentioned']
                            ) {
                                $productChange = true;
                            }
                            if ($oldProductDescription) {
                                $oldProductDescription->delete();
                            }
                        }
                        //然后新增
                        $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                    } else {
                        //直接更新
                        $newProductDescriptionUpdate = $newProductDescription->where('product_id', $product['id'])
                            ->first();
                        if ($newProductDescriptionUpdate) {
                            $descriptionRecord = $newProductDescriptionUpdate->updateWithAttributes($itemDescription);
                        } else {
                            $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                        }
                        // 属性是否有变动
                        if (
                            $newProductDescriptionUpdate
                            && $newProductDescriptionUpdate['companies_mentioned']
                            == $itemDescription['companies_mentioned']
                        ) {
                            $productChange = true;
                        }
                    }
                    $updateCount++;
                    $updateDetail .= "编号:{$item['third_sync_id']};报告id:{$product['id']};【{$row['name']}】" . "\r\n";
                } else {
                    //新增报告
                    $product = Products::create($item);
                    $forProductId = $product['id'];
                    //新增报告详情
                    $newProductDescription = (new ProductsDescription($newYear));
                    $itemDescription['product_id'] = $product['id'];
                    $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                    $insertCount++;
                    $insertDetail .= "编号:{$item['third_sync_id']};报告id:{$product['id']};【{$row['name']}】" . "\r\n";
                }
                //执行到这里算是操作成功的
                array_push($succIdList, $forProductId);
                array_push($thirdIdList, $row['id']);
                //                if (!empty($row['id'])) {
                //                    //维护xunSearch索引, 队列执行
                //                    $this->pushSyncSphinxQueue($row['id'], $site);
                //                }
                // 发帖课题
                if (!empty($product['id'])) {
                    try {
                        // 修改课题
                        $postSubject = postSubject::query()->where('product_id', $product['id'])->first();
                        $postSubjectUpdate = [];
                        $postSubjectUpdate['name'] = $item['name'];
                        $postSubjectUpdate['type'] = postSubject::TYPE_POST_SUBJECT;
                        $postSubjectUpdate['product_id'] = $product['id'];
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
                            postSubject::query()->where('product_id', $product['id'])->update($postSubjectUpdate);
                        } else {
                            postSubject::create($postSubjectUpdate);
                        }
                    } catch (\Throwable $psth) {
                    }
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            $details .= "【错误】编号:{$item['third_sync_id']}:   " . '【' . ($item['name']) . '】' . $th->getMessage() . "\r\n";
            // $details = $th->getLine().$th->getMessage().$th->getTraceAsString() . "\r\n";
            // $details = json_encode($row) . "\r\n";
            $errorCount++;
            array_push($errIdList, $row['id']);
        }
        //恢复监听
        Products::setEventDispatcher($dispatcher);
        //批量推送sphinx索引
        $this->PushSphinxQueueByIdList($succIdList, $site);
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
        $this->notifyThirdRes($thirdIdList, $errIdList);
    }

    /**
     *  推送sphinx队列
     */
    public function PushSphinxQueueByIdList($product_id_list, $site) {
        if (empty($product_id_list)) {
            return false;
        }
        $product_id_list = array_unique($product_id_list);
        $data = [
            'class'  => 'Modules\Site\Http\Controllers\ProductsUploadLogController',
            'method' => 'SyncSphinxByIdList',
            'site'   => $site,
            'data'   => $product_id_list,
        ];
        $data = json_encode($data);
        SyncSphinxIndex::dispatch($data)->onQueue(QueueConst::SYNC_SPGINX_INDEX);
    }

    public function notifyThirdRes($sucIdList, $errIdList) {
        //调用接口反馈成功, 失败问题
        $url = $this->syncConfig[$this->notifyDataResUrlKey];
        $token = $this->syncConfig[$this->notifyDataSyncTokenKey];
        $table = $this->syncConfig[$this->notifyDataResTableKey];
        if (empty($url) || empty($token) || empty($table)) {
            \Log::error('同步通知接口配置错误,请联系管理员');
            throw new \Exception("notify res url config error");

            return false;
        }
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
            'table'      => $table,
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
                \Log::error('返回结果数据:'.json_encode([$respData]).'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
                return true;
            } else {
                \Log::error('请求接口失败,请联系管理员:'.json_encode([$url, $token, $respData]));
                throw new \Exception('请求通知接口失败,请联系管理员');
            }
        } catch (Exception $e) {
            \Log::error('返回结果数据:'.$e->getMessage().'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
            // 处理异常
            throw $e;
        }
    }

    public function checkProductName($productName) {
        // 忽略报告名为空的数据
        if (empty($productName)) {
            return trans('lang.product_name_empty')."\r\n";
        }
        // 含有敏感词的报告需要过滤
        //        $matchSenWord = $this->checkFitter($productName);
        //        if (!empty($matchSenWord)) {
        //            return "该报告名称{$productName}含有 {$matchSenWord} 敏感词,请检查\r\n";
        //        }
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
            //if (mb_strpos($name, $fillterRules) !== false) { //中文比对
            if (strcasecmp($name, $fillterRules) == 0) { //英文比对
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

    public function pushSyncSphinxQueue($productId, $site) {
        $data = [
            'class'  => 'Modules\Site\Http\Controllers\ProductsUploadLogController',
            'method' => 'xsSyncProductIndex',
            'site'   => $site,
            'data'   => $productId,
        ];
        $data = json_encode($data);
        SyncSphinxIndex::dispatch($data)->onQueue(QueueConst::SYNC_SPGINX_INDEX);
    }

    /**
     *
     *
     * @return mixed
     */
    private function pullProductData($isCrontab = false) {
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
            App::setLocale('zh');
            //如果来自定时任务那一端, 需要判断是否开启自动同步开关
            if ($isCrontab) {
                $autoSyncDataVal = SystemValue::query()
                                              ->where("key", $this->autoSyncDataKey)
                                              ->value('value');
                //如果没有开启自动同步开关, 那么直接退出
                if (empty($autoSyncDataVal) && $autoSyncDataVal != self::$openAutoSyncData) {
                    throw new \Exception("no open auto sync data config");

                    return false;
                }
            }
            //获取配置数据
            $keyList = [
                $this->syncProductUrlKey,
                $this->syncProductTokenKey,
                $this->notifyDataResUrlKey,
                $this->notifyDataSyncTokenKey,
                $this->notifyDataResTableKey
            ];
            $this->syncConfig = SystemValue::query()->whereIn("key", $keyList)->pluck('value', 'key')->toArray();
            $url = $this->syncConfig[$this->syncProductUrlKey];
            $token = $this->syncConfig[$this->syncProductTokenKey];
            if (empty($url) || empty($token)) {
                \Log::error('同步接口配置错误,请联系管理员');
                throw new \Exception("sync products url config error");

                return false;
            }
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
                $this->newHandlerRespData($respDataList, $site);
                \Log::error('同步完成');
            } else {
                throw new \Exception("请求接口失败,请联系管理员");
                \Log::error('请求接口失败,请联系管理员:'.json_encode([$url, $token, $respData]));
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
