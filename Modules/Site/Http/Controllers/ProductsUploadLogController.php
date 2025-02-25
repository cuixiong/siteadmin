<?php

namespace Modules\Site\Http\Controllers;

use App\Const\QueueConst;
use App\Jobs\HandlerProductExcel;
use App\Jobs\SyncSphinxIndex;
use App\Jobs\UploadProduct;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Site\Http\Models\ProductsUploadLog;
use Modules\Site\Http\Models\Region;
use App\xlsx\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\ProductsExcelField;
use Modules\Site\Http\Models\SensitiveWords;
use Modules\Site\Services\SenWordsService;

class ProductsUploadLogController extends CrudController {
    public $productCategory = [];
    public $regionList      = [];
    public $senWords        = [];

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
            $ModelInstance = $this->ModelInstance();
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
            $listSelect = ['id', 'file', 'count', 'insert_count', 'update_count', 'error_count', 'created_at',
                           'created_by', 'updated_at', 'updated_by', 'state', 'sort'];
            $model = $model->select($listSelect);
            //$model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $record = $model->get()->toArray();
            foreach ($record as &$forValue) {
                if (empty($forValue['updated_at'])) {
                    $costTime = 0;
                } else {
                    $costTime = strtotime($forValue['updated_at']) - strtotime($forValue['created_at']);
                }
                $forValue['costTime'] = $this->convertMinutes($costTime);
            }
            $data = [
                'total' => $total,
                'list'  => $record
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
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
            $record = $this->ModelInstance()->findOrFail($request->id);
            if(!empty($record ) && !empty($record->details )){
                $record->details = nl2br($record->details);
            }
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 获取该站点的出版商,下拉选择框数据
     *
     * @param $request 请求信息
     */
    public function getPublisher(Request $request) {
        $site = $request->header('Site');
        $publisherIds = Site::where('name', $site)->value('publisher_id');
        $data = [];
        if ($publisherIds) {
            $publisherIdArray = explode(',', $publisherIds);
            $data = (new Publisher())->GetListLabel(['id as value', 'name as label'], false, '',
                                                    ['status' => 1, 'id' => $publisherIdArray]);
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 批量上传报告
     *
     * @param $request 请求信息
     */
    public function uploadProducts(Request $request) {
        $basePath = public_path().'/site';
        $basePath .= '/'.$request->header('Site').'/';
        //检验目录是否存在
        if (!is_dir($basePath)) {
            @mkdir($basePath, 0777, true);
        }
        // 出版商id
        $publisher_id = $request->pulisher_id;
        // 操作者id
        $user = \Illuminate\Support\Facades\Auth::user();
        if (isset($user->id)) {
            $userID = $user->id;
        } else {
            $userID = 0;
        }
        //上传文件路径
        $pathsStr = $request->path;
        if (empty($publisher_id) || empty($pathsStr)) {
            ReturnJson(true, trans('lang.param_empty'));
        }
        $paths = explode(',', $pathsStr);
        $logIds = [];
        foreach ($paths as $key => $value) {
            if (strpos($value, '.aliyuncs.com') !== false) {
                //阿里云oss, 直接获取文件路径
                $path = $value;
            } else {
                $path = $basePath.$value;
            }
            //上传记录初始化,每个文件单独一条记录
            $logModel = ProductsUploadLog::create([
                                                      'file' => $value
                                                  ]);
            $logIds[] = $logModel->id;
            $data = [
                'class'        => 'Modules\Site\Http\Controllers\ProductsUploadLogController',
                'method'       => 'handleExcelFile',
                'site'         => $request->header('Site') ?? '',   //站点名称
                'log_id'       => $logModel->id,  //写入日志的id
                'data'         => $path,    //传递文件路径
                'publisher_id' => $publisher_id,  //出版商id
                'user_id'      => $userID,  //用户id
            ];
            $data = json_encode($data);
//            $RabbitMQ = new RabbitmqService();
//            $RabbitMQ->setQueueName('products-file-queue'); // 设置队列名称
//            $RabbitMQ->setExchangeName('Products'); // 设置交换机名称
//            $RabbitMQ->setQueueMode('direct'); // 设置队列模式
//            $RabbitMQ->setRoutingKey('productsKey1');
//            $RabbitMQ->push($data); // 推送数据
            dispatch(new UploadProduct($data))->onQueue(QueueConst::QUEEU_UPLOAD_PRODUCT);
        }
        $logIds = implode(',', $logIds);
        ReturnJson(true, trans('lang.request_success'), $logIds);
    }

    /**
     * 批量上传报告/队列处理文件
     *
     * @param $params
     */
    public function handleExcelFile($params = null) {
        ini_set('memory_limit', '4096M');
        try {
            // 设置当前租户
            tenancy()->initialize($params['site']);
            $logModel = ProductsUploadLog::where(['id' => $params['log_id']])->first();
            if (empty($logModel)) {
                throw new \Exception('日志记录不存在');
            }
            //读取文件
            $path = $params['data'];
            //获取表头与字段关系
            $fieldData = ProductsExcelField::where(['status' => 1])
                                           ->orderBy('sort', 'asc')
                                           ->pluck('field')
                                           ->toArray();
            $fieldSort = array_keys($fieldData);
            $reader = ReaderEntityFactory::createXLSXReader();
            $reader->setShouldPreserveEmptyRows(true);
            $reader->setShouldFormatDates(true);
            $reader->open($path);   //读取文件
            $excelData = [];
            foreach ($reader->getSheetIterator() as $sheetKey => $sheet) {
                foreach ($sheet->getRowIterator() as $rowKey => $sheetRow) {
                    if ($rowKey == 1) {
                        //表头跳过
                        continue;
                    }
                    $tempRow = $sheetRow->toArray(); //单行数据
                    $row = [];
                    foreach ($tempRow as $tempKey => $tempValue) {
                        if (in_array($tempKey, $fieldSort)) {
                            $field = $fieldData[$tempKey];
                            if ($field == 'name' && empty($tempValue)) {
                                //没有报告名称直接过滤
                                break;
                            }
                            $row[$field] = $tempValue;
                        }
                    }
                    if (!empty($row)) {
                        $excelData[] = $row;
                    }
                }
            }
            //加入队列
            if (!empty($excelData) && count($excelData) > 0) {
                //昵称去重
                $uniqueDataList = [];
                $authorCheck = ['已售报告', '完成报告'];
                foreach ($excelData as $forParamsData) {
                    //已售报告>完成报告>人名作者
                    if (!empty($uniqueDataList[$forParamsData['name']])) {
                        if (!in_array($uniqueDataList[$forParamsData['name']]['author'], $authorCheck)
                            && in_array($forParamsData['author'], $authorCheck)
                        ) {
                            //作者报告需要被这种报告替换
                            $uniqueDataList[$forParamsData['name']] = $forParamsData;
                        } elseif (in_array($uniqueDataList[$forParamsData['name']]['author'], $authorCheck)
                                  && $forParamsData['author'] == '已售报告') {
                            $uniqueDataList[$forParamsData['name']] = $forParamsData;
                        } elseif ($uniqueDataList[$forParamsData['name']]['author'] == $forParamsData['author']) {
                            $uniqueDataList[$forParamsData['name']] = $forParamsData;
                        }
                    } else {
                        $uniqueDataList[$forParamsData['name']] = $forParamsData;
                    }
                }
                $uniqueDataList = array_values($uniqueDataList);
                //记录任务状态、总数量
                $logData = [
                    'count' => count($uniqueDataList),
                    'state' => ProductsUploadLog::UPLOAD_READY,
                ];
                $logModel->update($logData);
                $data = [
                    'site'         => $params['site'],
                    'log_id'       => $params['log_id'],
                    'publisher_id' => $params['publisher_id'],
                    'data'         => $uniqueDataList,
                    'user_id'      => $params['user_id'],
                ];
                $this->handleNewProducts($data);
//                $groupData = array_chunk($excelData, 1000);
//                foreach ($groupData as $item) {
//                    $data = [
//                        'class'        => 'Modules\Site\Http\Controllers\ProductsUploadLogController',
//                        'method'       => 'handleProducts',
//                        'site'         => $params['site'],
//                        'log_id'       => $params['log_id'],
//                        'publisher_id' => $params['publisher_id'],
//                        'data'         => $item,
//                        'user_id'      => $params['user_id'],
//                    ];
//                    $data = json_encode($data);
//                    HandlerProductExcel::dispatch($data)->onQueue(QueueConst::QUEEU_HANDLER_PRODUCT_EXCEL);
//                }
            } else {
                throw new \Exception('excel文件没有数据');
            }
        } catch (\Exception $th) {
            // file_put_contents('ddddddddddd.txt', $th->getLine() . $th->getMessage() . $th->getTraceAsString(), FILE_APPEND);
            throw $th;
        }
    }

    /**
     * 批量上传报告/队列消费
     *
     * @param $params ['data'] 报告数据
     * @param $params ['site'] 站点
     */
    public function handleNewProducts($params = null) {
        if (empty($params['site'])) {
            throw new \Exception("site is empty", 1);
        }
        // 设置当前租户
        tenancy()->initialize($params['site']);
        App::setLocale('zh');
        $product_model = new Products();
        $publisher_id = $params['publisher_id'];
        $user_id = $params['user_id'];
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
        $i = 0;
        $groupData = array_chunk($params['data'], 200);
        foreach ($groupData as $groupArr) {
//            $i++;
//            echo '开始处理第'.$i.'组(200条)数据'.microtime(true).PHP_EOL;
            $insertCount = 0;
            $updateCount = 0;
            $errorCount = 0;
            $details = '';
            $sphinx_product_id_list = [];
            $pro_name_list = array_column($groupArr, 'name');
            $pro_ename_list = array_column($groupArr, 'english_name');
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
            foreach ($groupArr as $row) {
                try {
                    // 表头
                    $item = [];
                    //出版商
                    $item['publisher_id'] = $publisher_id;
                    //操作用户
                    $item['created_by'] = $user_id;
                    $item['updated_by'] = $user_id;
                    // 报告名称
                    $item['name'] = $row['name'] ?? '';
                    //校验报告名称
                    $checkMsg = $this->checkProductName($item['name']);
                    if ($checkMsg) {
                        $details .= $checkMsg;
                        $errorCount++;
                        continue;
                    }
                    // 报告名称(英)
                    $item['english_name'] = $row['english_name'] ?? '';
                    //作者
                    $item['author'] = $row['author'] ?? '';
                    // 过滤不符合作者覆盖策略的数据
                    $product = [];
                    if (!empty($productList[$item['name']])) {
                        $product = $productList[$item['name']];
                        if (($product['author'] == '已售报告' && $item['author'] != '已售报告')
                            || ($product['author'] == '完成报告'
                                && ($item['author'] != '已售报告'
                                    && $item['author'] != '完成报告'))
                        ) {
                            $details .= '【'.($row['name']).'】'.($item['author']).'-'.trans('lang.author_level')
                                        .($product['author'])."\r\n";
                            $errorCount++;
                            continue;
                        }
                    }
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
                        continue;
                    }
                    // 出版时间
//                if (!empty($row['published_date'])) {
//                    if (is_numeric($row['published_date'])) {
//                        $item['published_date'] = $row['published_date'];
//                    } elseif (is_string($row['published_date'])) {
//                        $item['published_date'] = strtotime($row['published_date']);
//                    } else {
//                        $item['published_date'] = 0;
//                    }
//                }
                    try {
                        // 出版时间
                        if (!empty($row['published_date'])) {
                            //转为 时间戳
                            $item['published_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp(
                                $row['published_date']
                            );
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    if (empty($item['published_date']) || $item['published_date'] < 0) {
                        $item['published_date'] = strtotime($row['published_date']);
                    }
                    // 忽略出版时间为空或转化失败的数据
                    if (empty($item['published_date']) || $item['published_date'] < 0) {
                        $details .= '【'.($row['name'] ?? '').'】'.trans('lang.published_date_empty')."\r\n";
                        $errorCount++;
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
                        continue;
                    }
                    //报告所属区域
                    $tempCountryId = $row['country_id'] ?? 0;
                    if (!empty($tempCountryId) && !empty($this->regionList[trim($tempCountryId)])) {
                        $item['country_id'] = intval($this->regionList[trim($tempCountryId)]);
                    } else {
                        $item['country_id'] = 0;
                    }
                    //关键词
                    $item['keywords'] = $row['keywords'] ?? '';
                    // 忽略关键词为空的数据
                    if (empty($item['keywords'])) {
                        $details .= '【'.($row['name']).'】'.trans('lang.keywords_empty')."\r\n";
                        $errorCount++;
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
                        continue;
                    }
                    //url链接也需要检测敏感词
                    $matchSenWord = $this->checkFitter($item['url']);
                    if (!empty($matchSenWord)) {
                        $details .= "该报告名称{$item['name']} , url: {$item['url']} ,含有 {$matchSenWord} 敏感词,请检查\r\n";
                        $errorCount++;
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
                    isset($row['description'])
                    && $itemDescription['description'] = str_replace('_x000D_', '', $row['description']);
                    isset($row['table_of_content'])
                    && $itemDescription['table_of_content'] = str_replace('_x000D_', '', $row['table_of_content']);
                    isset($row['tables_and_figures'])
                    && $itemDescription['tables_and_figures'] = str_replace('_x000D_', '', $row['tables_and_figures']);
                    isset($row['description_en'])
                    && $itemDescription['description_en'] = str_replace('_x000D_', '', $row['description_en']);
                    isset($row['table_of_content_en'])
                    &&
                    $itemDescription['table_of_content_en'] = str_replace('_x000D_', '', $row['table_of_content_en']);
                    isset($row['tables_and_figures_en'])
                    && $itemDescription['tables_and_figures_en'] = str_replace(
                        '_x000D_', '', $row['tables_and_figures_en']
                    );
                    isset($row['companies_mentioned'])
                    &&
                    $itemDescription['companies_mentioned'] = str_replace('_x000D_', '', $row['companies_mentioned']);
                    //新增详情字段
                    isset($row['definition'])
                    && $itemDescription['definition'] = str_replace('_x000D_', '', $row['definition']);
                    isset($row['overview'])
                    && $itemDescription['overview'] = str_replace('_x000D_', '', $row['overview']);
                    $item['year'] = date('Y', $item['published_date']);
//                $redisKey = 'upload_products_'.$item['name'];
//                if(!Redis::setnx($redisKey , 1)){
//                    //设置失败,休眠2s
//                    sleep(2);
//                }
                    //新纪录年份
                    $newYear = Products::publishedDateFormatYear($item['published_date']);
                    /**
                     * 数据库操作
                     */
                    if (!empty($product['id'])) {
                        unset($item['created_by']);
                        $itemDescription['product_id'] = $product['id'];
                        //旧纪录年份
                        $oldPublishedDate = $product['published_date'];
                        $oldYear = Products::publishedDateFormatYear($oldPublishedDate);
                        //更新报告
                        $product_model->where("id", $product['id'])->update($item);
                        $newProductDescription = (new ProductsDescription($newYear));
                        //出版时间年份更改
                        if ($oldYear != $newYear) {
                            //删除旧详情
                            if ($oldYear) {
                                $oldProductDescription = (new ProductsDescription($oldYear))->where(
                                    'product_id', $product['id']
                                )->first();
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
                                $descriptionRecord = $newProductDescriptionUpdate->updateWithAttributes(
                                    $itemDescription
                                );
                            } else {
                                $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                            }
                        }
                        $sphinx_product_id_list[] = $product['id'];
                        $updateCount++;
                    } else {
                        //新增报告
                        unset($item['updated_by']);
                        $product = Products::create($item);
                        //新增报告详情
                        $newProductDescription = (new ProductsDescription($newYear));
                        $itemDescription['product_id'] = $product['id'];
                        $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                        $insertCount++;
                        $sphinx_product_id_list[] = $product['id'];
                    }
                } catch (\Throwable $th) {
                    $details .= '【'.($row['name']).'】'.$th->getMessage()."\r\n";
                    $errorCount++;
                }
            }
            try {
                //同步当前组的sphinx
                $this->PushSphinxQueueByIdList($sphinx_product_id_list, $params['site']);
                $logModel = ProductsUploadLog::where(['id' => $params['log_id']])->first();
                $logData = [
                    'insert_count' => DB::raw("insert_count + {$insertCount}"),
                    'update_count' => DB::raw("update_count + {$updateCount}"),
                    'error_count'  => DB::raw("error_count + {$errorCount}"),
                    'details'      => ($logModel->details ?? '').$details,
                ];
                //使用redis, 保证多线程数据安全
                $totCnt = $insertCount + $updateCount + $errorCount;
                $redisKey = $params['site'].'_product_upload_log_id_'.$params['log_id'];
                //如果数量吻合，则证明上传完成了
                if ($logModel->count == Redis::incrby($redisKey, $totCnt)) {
                    $logData['state'] = ProductsUploadLog::UPLOAD_COMPLETE;
                } else {
                    $logData['state'] = ProductsUploadLog::UPLOAD_RUNNING;
                }
                $logFlag = $logModel->update($logData);
//            if ($logFlag) {
//                DB::commit();
//            } else {
//                DB::rollBack();
//            }
            } catch (\Exception $e) {
                //DB::rollBack();
                throw $e;
            }
            //echo '结束处理第'.$i.'组(200条)数据'.microtime(true).PHP_EOL;
        }
        //恢复监听
        Products::setEventDispatcher($dispatcher);
    }

    /**
     * 批量上传报告/队列消费
     *
     * @param $params ['data'] 报告数据
     * @param $params ['site'] 站点
     */
    public function handleProducts($params = null) {
        if (empty($params['site'])) {
            throw new \Exception("site is empty", 1);
        }
        // 设置当前租户
        tenancy()->initialize($params['site']);
        App::setLocale('zh');
        $publisher_id = $params['publisher_id'];
        $user_id = $params['user_id'];
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
        foreach ($params['data'] as $row) {
            try {
                // 表头
                $item = [];
                //出版商
                $item['publisher_id'] = $publisher_id;
                //操作用户
                $item['created_by'] = $user_id;
                $item['updated_by'] = $user_id;
                // 报告名称
                $item['name'] = $row['name'] ?? '';
                //校验报告名称
                $checkMsg = $this->checkProductName($item['name']);
                if ($checkMsg) {
                    $details .= $checkMsg;
                    $errorCount++;
                    continue;
                }
                // 报告名称(英)
                $item['english_name'] = $row['english_name'] ?? '';
                // 英文昵称含有敏感词的报告需要过滤
//                $matchSenWord = $this->checkFitter($item['english_name']);
//                if (!empty($matchSenWord)) {
//                    $details .= "该英文报告名称:{$item['english_name']}含有 {$matchSenWord} 敏感词,请检查\r\n";
//                    $errorCount++;
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
                    $details .= '【'.($row['name']).'】'.trans('lang.price_empty')."\r\n";
                    $errorCount++;
                    continue;
                }
                // 出版时间
//                if (!empty($row['published_date'])) {
//                    if (is_numeric($row['published_date'])) {
//                        $item['published_date'] = $row['published_date'];
//                    } elseif (is_string($row['published_date'])) {
//                        $item['published_date'] = strtotime($row['published_date']);
//                    } else {
//                        $item['published_date'] = 0;
//                    }
//                }
                try {
                    // 出版时间
                    if (!empty($row['published_date'])) {
                        //转为 时间戳
                        $item['published_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp(
                            $row['published_date']
                        );
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
                if (empty($item['published_date']) || $item['published_date'] < 0) {
                    $item['published_date'] = strtotime($row['published_date']);
                }
                // 忽略出版时间为空或转化失败的数据
                if (empty($item['published_date']) || $item['published_date'] < 0) {
                    $details .= '【'.($row['name'] ?? '').'】'.trans('lang.published_date_empty')."\r\n";
                    $errorCount++;
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
                    continue;
                }
                //url链接也需要检测敏感词
                $matchSenWord = $this->checkFitter($item['url']);
                if (!empty($matchSenWord)) {
                    $details .= "该报告名称{$item['name']} , url: {$item['url']} ,含有 {$matchSenWord} 敏感词,请检查\r\n";
                    $errorCount++;
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
                isset($row['description'])
                && $itemDescription['description'] = str_replace('_x000D_', '', $row['description']);
                isset($row['table_of_content'])
                && $itemDescription['table_of_content'] = str_replace('_x000D_', '', $row['table_of_content']);
                isset($row['tables_and_figures'])
                && $itemDescription['tables_and_figures'] = str_replace('_x000D_', '', $row['tables_and_figures']);
                isset($row['description_en'])
                && $itemDescription['description_en'] = str_replace('_x000D_', '', $row['description_en']);
                isset($row['table_of_content_en'])
                && $itemDescription['table_of_content_en'] = str_replace('_x000D_', '', $row['table_of_content_en']);
                isset($row['tables_and_figures_en'])
                &&
                $itemDescription['tables_and_figures_en'] = str_replace('_x000D_', '', $row['tables_and_figures_en']);
                isset($row['companies_mentioned'])
                && $itemDescription['companies_mentioned'] = str_replace('_x000D_', '', $row['companies_mentioned']);
                //新增详情字段
                isset($row['definition'])
                && $itemDescription['definition'] = str_replace('_x000D_', '', $row['definition']);
                isset($row['overview']) && $itemDescription['overview'] = str_replace('_x000D_', '', $row['overview']);
                $item['year'] = date('Y', $item['published_date']);
//                $redisKey = 'upload_products_'.$item['name'];
//                if(!Redis::setnx($redisKey , 1)){
//                    //设置失败,休眠2s
//                    sleep(2);
//                }
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
                        continue;
                    }
                }
                //测试要求导入报告, 默认 热门 + 精品
//                $item['show_hot'] = 1;
//                $item['show_recommend'] = 1;
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
                if (!empty($product)) {
                    //维护xunSearch索引, 队列执行
                    $description = $row['description'] ?? '';
                    $this->pushSyncSphinxQueue($product, $params['site']);
                }
            } catch (\Throwable $th) {
                //throw $th;
                $details .= '【'.($row['name']).'】'.$th->getMessage()."\r\n";
                // $details = $th->getLine().$th->getMessage().$th->getTraceAsString() . "\r\n";
                // $details = json_encode($row) . "\r\n";
                $errorCount++;
            }
//            if(!empty($redisKey )){
//                Redis::del($redisKey);
//            }
        }
        //恢复监听
        Products::setEventDispatcher($dispatcher);
        try {
//            DB::beginTransaction();
            $logModel = ProductsUploadLog::where(['id' => $params['log_id']])->first();
            $insertCnt = ($logModel->insert_count ?? 0) + $insertCount;
            $updCnt = ($logModel->update_count ?? 0) + $updateCount;
            $errCnt = ($logModel->error_count ?? 0) + $errorCount;
            $logData = [
                'insert_count' => DB::raw("insert_count + {$insertCount}"),
                'update_count' => DB::raw("update_count + {$updateCount}"),
                'error_count'  => DB::raw("error_count + {$errorCount}"),
                'details'      => ($logModel->details ?? '').$details,
            ];
            //使用redis, 保证多线程数据安全
            $totCnt = $insertCount + $updateCount + $errorCount;
            $redisKey = 'product_upload_log_id_'.$params['log_id'];
            //如果数量吻合，则证明上传完成了
            if ($logModel->count == Redis::incrby($redisKey, $totCnt)) {
                $logData['state'] = ProductsUploadLog::UPLOAD_COMPLETE;
            } else {
                $logData['state'] = ProductsUploadLog::UPLOAD_RUNNING;
            }
            $logFlag = $logModel->update($logData);
//            if ($logFlag) {
//                DB::commit();
//            } else {
//                DB::rollBack();
//            }
        } catch (\Exception $e) {
            //DB::rollBack();
            throw $e;
        }
    }

    /**
     * 上传进度
     *
     * @param $request 请求信息
     */
    public function uploadProcess(Request $request) {
        $logIds = $request->ids;
        if (empty($logIds)) {
            ReturnJson(true, trans('lang.param_empty'));
        }
        $logIdsArray = explode(',', $logIds);
        // return $logIdsArray;
        $logData = ProductsUploadLog::whereIn('id', $logIdsArray)->get()->toArray();
        $data = [
            'result' => true,
            'msg'    => '',
        ];
        $text = '';
        $updateTime = 0;
        foreach ($logData as $key => $value) {
            if ($value['state'] != ProductsUploadLog::UPLOAD_COMPLETE) {
                $data['result'] = false;
            }
            $updatedTimestamp = strtotime($value['updated_at']);
            if ($updatedTimestamp > $updateTime) {
                $updateTime = $updatedTimestamp;
            }
            switch ($value['state']) {
                case ProductsUploadLog::UPLOAD_INIT:
                    $text .= '【'.$value['file'].'】'.trans('lang.upload_init_msg')."\r\n";
                    break;
                case ProductsUploadLog::UPLOAD_READY:
                    $text .= '【'.$value['file'].'】'.trans('lang.upload_ready_msg')."\r\n";
                    break;
                case ProductsUploadLog::UPLOAD_RUNNING:
                    $text .= '【'.$value['file'].'】'.trans('lang.upload_running_msg').($value['insert_count']
                                                                                      + $value['update_count']
                                                                                      + $value['error_count']).'/'
                             .$value['count']."\r\n";
                    break;
                case ProductsUploadLog::UPLOAD_COMPLETE:
                    $text .= '【'.$value['file'].'】'.trans('lang.upload_complete_msg')."\r\n";
                    break;
                default:
                    # code...
                    break;
            }
        }
        $data['msg'] = $text;
        //五分钟没反应则提示
        if (time() > $updateTime + 86400) {
            $data = [
                'result' => true,
                'msg'    => trans('lang.time_out'),
            ];
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 上传示例文件,根据设置excel字段生成
     *
     * @param $request 请求信息
     */
    public function exampleFile(Request $request) {
        // $site = $request->header('Site');
        // if (empty($site)) {
        //     ReturnJson(TRUE, trans('lang.param_empty'));
        // }
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser('sample.xlsx'); // 将文件输出到浏览器并下载
        //获取表头与字段关系
        $fieldData = ProductsExcelField::where(['status' => 1])->select(['name', 'field'])->orderBy('sort', 'asc')->get(
        )->toArray();
        $title = array_column($fieldData, 'name');
        foreach ($fieldData as $key => $value) {
            $fieldData[$key]['sort'] = $key;
        }
        $fieldData = array_column($fieldData, 'field', 'sort');
        //写入标题
        $style = (new StyleBuilder())->setShouldWrapText(false)->build();
        $row = WriterEntityFactory::createRowFromArray($title, $style);
        $writer->addRow($row);
        //读取几条数据当案例
        $record = Products::orderBy('id', 'desc')->limit(5)->get()->makeHidden((new Products())->getAppends())->toArray(
        );
        if ($record && count($record) > 0) {
            foreach ($record as $key => $item) {
                $year = date('Y', $item['published_date']);
                if (empty($year) || !is_numeric($year) || strlen($year) !== 4) {
                    continue;
                }
                $item['published_date'] = date('Y-m-d', $item['published_date']) ?? '';
                if (isset($item['category_id'])) {
                    $item['category_id'] = ProductsCategory::where('id', $item['category_id'])->value('name') ?? '';
                }
                $descriptionData = (new ProductsDescription($year))->where('product_id', $item['id'])->first();
                $item['description'] = $descriptionData['description'] ?? '';
                $item['table_of_content'] = $descriptionData['table_of_content'] ?? '';
                $item['tables_and_figures'] = $descriptionData['tables_and_figures'] ?? '';
                $item['description_en'] = $descriptionData['description_en'] ?? '';
                $item['table_of_content_en'] = $descriptionData['table_of_content_en'] ?? '';
                $item['tables_and_figures_en'] = $descriptionData['tables_and_figures_en'] ?? '';
                $item['companies_mentioned'] = $descriptionData['companies_mentioned'] ?? '';
                $row = [];
                foreach ($fieldData as $value) {
                    if (empty($value) || !isset($item[$value])) {
                        $row[] = '';
                    } else {
                        $row[] = $item[$value];
                    }
                }
                $rowFromValues = WriterEntityFactory::createRowFromArray($row, $style);
                $writer->addRow($rowFromValues);
            }
        }
        $writer->close();
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

    public function checkProductName($productName) {
        // 忽略报告名为空的数据
        if (empty($productName)) {
            return trans('lang.name_empty')."\r\n";
        }
        // 含有敏感词的报告需要过滤
//        $matchSenWord = $this->checkFitter($productName);
//        if (!empty($matchSenWord)) {
//            return "该报告名称:{$productName}含有 {$matchSenWord} 敏感词,请检查\r\n";
//        }
        return false;
    }

    /**
     * 讯搜废弃
     * @param $product
     * @param $description
     * @param $site
     *
     */
//    private function pushXsSyncQueue($product, $description, $site): void {
//        $xsProductData = $product->toArray();
//        $xsProductData['description'] = $description ?? '';
//        $data = [
//            'class'  => 'Modules\Site\Http\Controllers\ProductsUploadLogController',
//            'method' => 'xsSyncProductIndex',
//            'site'   => $site,
//            'data'   => $xsProductData,
//        ];
//        $data = json_encode($data);
//        $RabbitMQ = new RabbitmqService();
//        $RabbitMQ->setQueueName('xssyncindex-queue'); // 设置队列名称
//        $RabbitMQ->setExchangeName('Products'); // 设置交换机名称
//        $RabbitMQ->setQueueMode('direct'); // 设置队列模式
//        $RabbitMQ->setRoutingKey('productsKey1');
//        $RabbitMQ->push($data); // 推送数据
//    }

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

    /**
     *  批量同步sphinx索引
     */
    public function SyncSphinxByIdList($params) {
        $product_id_list = $params['data'];
        try {
            (new Products())->batchUpdateSphinx($product_id_list, $params['site']);
        } catch (\Exception $e) {
            \Log::error('批量同步sphinx索引异常:'.$e->getMessage().'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
            echo '批量同步sphinx索引异常:'.$e->getMessage().PHP_EOL;
        }

        return true;
    }

    public function pushSyncSphinxQueue($product, $site) {
        return false;
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

    public function xsSyncProductIndex($params) {
        $dataId = $params['data'];
        //不想多维护一遍, 宁愿再查一遍
        try {
            (new Products())->excuteSphinxReq($dataId, 'update', $params['site']);
        } catch (\Exception $e) {
            \Log::error('返回结果数据:'.$e->getMessage().'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
        }

        //(new Products())->excuteXs($params['site'], 'update', $handlerData);
        return true;
    }

    public function isDecimalString($str) {
        // 允许小数点和小数点后的数字
        $pattern = '/^\d+(\.\d+)?%$/';

        return preg_match($pattern, $str) === 1;
    }

    public function convertMinutes($seconds) {
        if ($seconds >= 60) {
            $minutes = intval($seconds / 60); // 获取分钟数
            $remainingSeconds = $seconds % 60; // 获取剩余的秒数
            // 如果剩余的秒数小于10，前面补0
            $remainingSeconds = str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT);
        } else {
            $minutes = 0;
            $remainingSeconds = $seconds;
        }

        return [
            'minutes' => $minutes,
            'seconds' => $remainingSeconds
        ];
    }
}
