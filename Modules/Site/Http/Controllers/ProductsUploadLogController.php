<?php

namespace Modules\Site\Http\Controllers;

use App\Imports\ProductsImport;
use App\Services\RabbitmqService;
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
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\ProductsExcelField;

class ProductsUploadLogController extends CrudController
{

    /**
     * 获取该站点的出版商,下拉选择框数据
     * @param $request 请求信息
     */
    public function getPublisher(Request $request)
    {

        $site = $request->header('Site');
        $publisherIdArray = Site::where('name', $site)->pluck('publisher_id')->toArray();
        $data = (new Publisher())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1, 'id' => $publisherIdArray]);

        ReturnJson(TRUE, trans('lang.request_success'), $data);
    }



    /**
     * 批量上传报告
     * @param $request 请求信息
     */
    public function uploadProducts(Request $request)
    {
        $basePath = public_path() . '/site';
        $basePath .= '/' . $request->header('Site');

        //检验目录是否存在
        if (!is_dir($basePath)) {
            @mkdir($basePath, 0777, true);
        }
        //出版商id
        $pulisher_id = $request->pulisher_id;
        //上传文件路径
        $pathsStr = $request->path;
        if (empty($pulisher_id) || empty($pathsStr)) {
            ReturnJson(TRUE, trans('lang.param_empty'));
        }


        $paths = explode(',', $pathsStr);
        // return $paths;
        // $productsImport = new ProductsImport();
        // $productsImport->site = $request->header('Site') ?? '';
        // $productsImport->log_id = $logModel->id;
        // foreach ($paths as $key => $value) {
        //     $path = $basePath . $value;
        //     // return $path;
        //     Excel::import($productsImport, $path);
        // }

        //获取表头与字段关系
        $fieldData = ProductsExcelField::where(['status' => 1])->where('field', '<>', '')->where('sort', '>', 0)->select(['field', 'sort'])->distinct()->get()->toArray();
        $fieldData = array_map(function ($item) {
            $item['sort'] =  $item['sort'] - 1;
            return $item;
        }, $fieldData);
        $fieldData = array_column($fieldData, 'field', 'sort');
        // $fieldSort = array_keys($fieldData);

        // return $fieldSort;
        // 获取总行数
        // $totalRows = 0;
        $logIds = [];
        foreach ($paths as $key => $value) {
            $path = $basePath . $value;

            //上传记录初始化,每个文件单独一条记录
            $logModel = ProductsUploadLog::create([
                'file' => $value
            ]);
            $logIds[] = $logModel->id;
            $data = [
                'class' => 'Modules\Site\Http\Controllers\ProductsUploadLogController',
                'method' => 'handleExcelFile',
                'site' => $request->header('Site') ?? '',   //站点名称
                'log_id' => $logModel->id,  //写入日志的id
                'data' => $path,    //传递文件路径
                'fieldData' => $fieldData,  //字段与excel表头的对应关系
                'pulisher_id' => $pulisher_id,  //出版商id
            ];
            $data = json_encode($data);
            $RabbitMQ = new RabbitmqService();
            $RabbitMQ->setQueueName('products-file-queue'); // 设置队列名称
            $RabbitMQ->setExchangeName('Products'); // 设置交换机名称
            $RabbitMQ->setQueueMode('direct'); // 设置队列模式
            $RabbitMQ->setRoutingKey('productsKey1');
            $RabbitMQ->push($data); // 推送数据
            // 打开 Excel 文件
            // $reader = ReaderEntityFactory::createXLSXReader();
            // $reader->setShouldPreserveEmptyRows(true);
            // $reader->open($path);

            // foreach ($reader->getSheetIterator() as $sheet) {
            //     // $totalRows += $sheet->getTotalRows();
            // }

            // // 关闭文件
            // $reader->close();
        }
        $logIds = implode(',', $logIds);
        // return $totalRows;
        ReturnJson(TRUE, trans('lang.request_success'), $logIds);
    }

    /**
     * 批量上传报告/队列处理文件
     * @param $params 
     */
    public function handleExcelFile($params = null)
    {
        ini_set('memory_limit', '4096M');
        try {
            //读取文件
            $path = $params['data'];
            $fieldData = $params['fieldData'];
            $fieldSort = array_keys($fieldData);
            $reader = ReaderEntityFactory::createXLSXReader($path);
            $reader->setShouldPreserveEmptyRows(true);
            $reader->setShouldFormatDates(true);
            $reader->open($path);   //读取文件
            $excelData = [];

            foreach ($reader->getSheetIterator() as $sheetKey => $sheet) {
                // if ($sheetKey != 1) {
                //     continue;
                // }
                foreach ($sheet->getRowIterator() as $rowKey => $sheetRow) {
                    if ($rowKey == 1) {
                        //表头跳过
                        continue;
                    }
                    $tempRow =  $sheetRow->toArray(); //单行数据
                    $row = [];
                    foreach ($tempRow as $tempKey => $tempValue) {
                        if (in_array($tempKey, $fieldSort)) {
                            $row[$fieldData[$tempKey]] = $tempValue;
                        }
                    }
                    if (count($row) > 0) {
                        $excelData[] = $row;
                    }
                }
            }
            // 设置当前租户
            tenancy()->initialize($params['site']);
            //记录任务状态、总数量
            $logModel = ProductsUploadLog::where(['id' => $params['log_id']])->first();
            $logData = [
                'count' => count($excelData),
                'state' => ProductsUploadLog::UPLOAD_READY,
            ];
            $logModel->update($logData);
            //加入队列
            if ($excelData && count($excelData) > 0) {
                $groupData = array_chunk($excelData, 100);
                foreach ($groupData as $item) {
                    $data = [
                        'class' => 'Modules\Site\Http\Controllers\ProductsUploadLogController',
                        'method' => 'handleProducts',
                        'site' => $params['site'],
                        'log_id' => $params['log_id'],
                        'pulisher_id' => $params['pulisher_id'],
                        'data' => $item
                    ];
                    $data = json_encode($data);
                    $RabbitMQ = new RabbitmqService();
                    $RabbitMQ->setQueueName('products-queue'); // 设置队列名称
                    $RabbitMQ->setExchangeName('Products'); // 设置交换机名称
                    $RabbitMQ->setQueueMode('direct'); // 设置队列模式
                    $RabbitMQ->setRoutingKey('productsKey2');
                    $RabbitMQ->push($data); // 推送数据
                }
            }

            //code...
        } catch (\Throwable $th) {
            // file_put_contents('C:\\Users\\Administrator\\Desktop\\ddddddddddd.txt', $params['log_id'], FILE_APPEND);
        }
    }


    /**
     * 批量上传报告/队列消费
     * @param $params['data'] 报告数据
     * @param $params['site'] 站点
     */
    public function handleProducts($params = null)
    {
        // exit;
        if (empty($params['site'])) {
            throw new \Exception("site is empty", 1);
        }

        // 设置当前租户
        tenancy()->initialize($params['site']);
        // tenancy()->initialize('QY_EN');
        $pulisher_id = $params['pulisher_id'];

        // $count = 0;
        $insertCount = 0;
        $updateCount = 0;
        $errorCount = 0;
        $details = '';

        foreach ($params['data'] as $row) {
            // $count++;
            try {
                // 表头
                $item = [];
                //出版商
                $item['pulisher_id'] = $pulisher_id;
                // 报告名称
                isset($row['name']) && $item['name'] = $row['name'];
                // 报告名称(英)
                isset($row['english_name']) && $item['english_name'] = $row['english_name'];
                // 页数
                isset($row['pages']) && $item['pages'] = $row['pages'];
                // 图表数
                isset($row['tables']) && $item['tables'] = $row['tables'];
                // 基础价
                isset($row['price']) && $item['price'] = $row['price'];

                try {
                    // 出版时间
                    isset($row['published_date']) && $item['published_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($row['published_date']); //转为 时间戳
                } catch (\Throwable $th) {
                    //throw $th;
                }
                if (!isset($item['published_date']) || $item['published_date'] < 0) {
                    $item['published_date'] = strtotime($row['published_date']);
                }
                // file_put_contents('C:\\Users\\Administrator\\Desktop\\123.txt',json_encode($item['published_date']),FILE_APPEND);

                // 报告分类
                isset($row['category_id']) && $item['category_id'] = ProductsCategory::where('name', trim($row['category_id']))->value('id') ?? 0;
                //报告所属区域
                isset($row['country_id']) && $item['country_id'] = Region::where('name', trim($row['country_id']))->value('id') ?? 0;
                //作者
                isset($row['author']) && $item['author'] = $row['author'];
                //关键词
                isset($row['keywords']) && $item['keywords'] = $row['keywords'];
                //自定义链接
                isset($row['url']) && $item['url'] = $row['url'];
                // 如果链接为空，则用关键词做链接
                if (!empty($row['keywords']) && empty($row['url'])) {
                    $item['url'] = $row['keywords'];
                    $item['url'] = strtolower(preg_replace('/%[0-9A-Fa-f]{2}/', '-', urlencode(str_replace(' ', '-', trim($item['url'])))));
                    $item['url'] = strtolower(preg_replace('/[^A-Za-z0-9-]/', '-', urlencode(str_replace(' ', '-', trim($item['url'])))));
                    $item['url'] = trim($item['url'], '-'); //左右可能有多余的横杠
                }

                //详情数据
                $itemDescription = [];
                isset($row['description']) && $itemDescription['description'] = str_replace('_x000D_', '', $row['description']);
                isset($row['table_of_content']) && $itemDescription['table_of_content'] = str_replace('_x000D_', '', $row['table_of_content']);
                isset($row['tables_and_figures']) && $itemDescription['tables_and_figures'] = str_replace('_x000D_', '', $row['tables_and_figures']);
                isset($row['description_en']) && $itemDescription['description_en'] = str_replace('_x000D_', '', $row['description_en']);
                isset($row['table_of_content_en']) && $itemDescription['table_of_content_en'] = str_replace('_x000D_', '', $row['table_of_content_en']);
                isset($row['tables_and_figures_en']) && $itemDescription['tables_and_figures_en'] = str_replace('_x000D_', '', $row['tables_and_figures_en']);
                isset($row['companies_mentioned']) && $itemDescription['companies_mentioned'] = str_replace('_x000D_', '', $row['companies_mentioned']);


                // 查询单个报告数据/去重
                $product = Products::where('name', trim($item['name']))->orWhere('name', isset($row['english_name']) ? trim($row['name']) : '')->first();

                /** 
                 * 不合格的数据过滤
                 */

                // 忽略报告名为空的数据
                if (empty($item['name'])) {
                    $details .= trans('lang.name_empty') . "\r\n";
                    $errorCount++;
                    continue;
                }
                // 忽略基础价为空的数据
                if (empty($item['price'])) {
                    $details .= '【' . ($row['name'] ?? '') . '】' . trans('lang.price_empty') . "\r\n";
                    $errorCount++;
                    continue;
                }
                // 忽略出版时间为空或转化失败的数据
                if (empty($item['published_date']) || $item['published_date'] < 0) {
                    $details .= '【' . ($row['name'] ?? '') . '】' . trans('lang.published_date_empty') . "\r\n";
                    continue;
                }
                // 忽略分类为空的数据
                if (empty($item['category_id'])) {
                    $details .= '【' . ($row['name'] ?? '') . '】' . $row['category_id'] . '-' . trans('lang.category_empty') . "\r\n";
                    $errorCount++;
                    continue;
                }
                // 过滤不符合作者策略的数据
                if ($product) {
                    if (
                        !($item['author'] == '完成报告'
                            || ($item['author'] == '报告翻新' && $product->author != '完成报告')
                            || ($product->author != '完成报告' && $product->author != '报告翻新'))
                    ) {
                        $details .= '【' . ($row['name'] ?? '') . '】' . ($item['author']) . '-' . trans('lang.author_level') . ($product->author) . "\r\n";
                        $errorCount++;
                        continue;
                    }
                }


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
                            $oldProductDescription = (new ProductsDescription($oldYear))->where('product_id', $product->id)->first();
                            $oldProductDescription->delete();
                        }
                        //然后新增
                        $descriptionRecord = $newProductDescription->saveWithAttributes($itemDescription);
                    } else {
                        //直接更新
                        $newProductDescription = $newProductDescription->where('product_id', $product->id)->first();
                        $descriptionRecord = $newProductDescription->updateWithAttributes($itemDescription);
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
                //code...
            } catch (\Throwable $th) {
                //throw $th;
                $details .= '【' . ($row['name'] ?? '') . '】' . $th->getMessage() . "\r\n";
                // $details = $th->getLine().$th->getMessage().$th->getTraceAsString() . "\r\n";
                // $details = json_encode($row) . "\r\n";
                $errorCount++;
            }
        }
        try {
            DB::beginTransaction();
            $logModel = ProductsUploadLog::where(['id' => $params['log_id']])->first();
            $logData = [
                // 'count' => ($logModel->count ?? 0) + $count,
                'insert_count' => ($logModel->insert_count ?? 0) + $insertCount,
                'update_count' => ($logModel->update_count ?? 0) + $updateCount,
                'error_count' => ($logModel->error_count ?? 0) + $errorCount,
                'details' => ($logModel->details ?? '')  . $details,

            ];
            //如果数量吻合，则证明上传完成了
            if ($logModel->count == $logData['insert_count'] + $logData['update_count'] + $logData['error_count']) {
                $logData['state'] = ProductsUploadLog::UPLOAD_COMPLETE;
            } else {
                $logData['state'] = ProductsUploadLog::UPLOAD_RUNNING;
            }
            $logFlag = $logModel->update($logData);

            if ($logFlag) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * 上传进度
     * @param $request 请求信息
     */
    public function uploadProcess(Request $request)
    {
        $logIds = $request->ids;
        if (empty($logIds)) {
            ReturnJson(TRUE, trans('lang.param_empty'));
        }
        $logIdsArray = explode(',', $logIds);
        // return $logIdsArray;
        $logData = ProductsUploadLog::whereIn('id', $logIdsArray)->get()->toArray();
        $data = [
            'result' => true,
            'msg' => '',
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
                    $text .= '【' . $value['file'] . '】' . '正在加载中...' . "\r\n";
                    break;

                case ProductsUploadLog::UPLOAD_READY:
                    $text .= '【' . $value['file'] . '】' . '正在等待执行...' . "\r\n";
                    break;

                case ProductsUploadLog::UPLOAD_RUNNING:
                    $text .= '【' . $value['file'] . '】' . '运行中,进度：' . ($value['insert_count'] + $value['update_count'] + $value['error_count']) . '/' . $value['count'] . "\r\n";
                    break;

                case ProductsUploadLog::UPLOAD_COMPLETE:
                    $text .= '【' . $value['file'] . '】' . '完成' . "\r\n";
                    break;

                default:
                    # code...
                    break;
            }
        }
        $data['msg'] = $text;
        //五分钟没反应则提示
        if (time() > $updateTime + 60 * 5) {

            $data = [
                'result' => true,
                'msg' => '超时',
            ];
        }


        ReturnJson(TRUE, trans('lang.request_success'), $data);
    }
}
