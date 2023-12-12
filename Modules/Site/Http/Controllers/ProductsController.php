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
use Modules\Site\Http\Models\ProductsExcelField;

class ProductsController extends CrudController
{

    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $pageSize 页数
     * @param Array $where 查询条件数组 默认空数组
     */
    protected function list(Request $request)
    {
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
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }

            $record = $model->get();

            //附加详情数据
            foreach ($record as $key => $item) {
                $year = date('Y', $item['published_date']);
                if (empty($year) || !is_numeric($year) || strlen($year) !== 4) {
                    continue;
                }
                $descriptionData = (new ProductsDescription($year))->where('product_id', $item['id'])->first();
                $record[$key]['description'] = $descriptionData['description'] ?? '';
                $record[$key]['table_of_content'] = $descriptionData['table_of_content'] ?? '';
                $record[$key]['tables_and_figures'] = $descriptionData['tables_and_figures'] ?? '';
                $record[$key]['description_en'] = $descriptionData['description_en'] ?? '';
                $record[$key]['table_of_content_en'] = $descriptionData['table_of_content_en'] ?? '';
                $record[$key]['tables_and_figures_en'] = $descriptionData['tables_and_figures_en'] ?? '';
                $record[$key]['companies_mentioned'] = $descriptionData['companies_mentioned'] ?? '';
            }

            //表头排序
            $headerTitle = (new ListStyle())->getHeaderTitle(class_basename($ModelInstance::class), $request->user->id);

            $data = [
                'total' => $total,
                'list' => $record,
                'headerTitle' => $headerTitle ?? [],
            ];
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 创建报告
     * @param Request $request
     */
    protected function store(Request $request)
    {

        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            // 开启事务
            DB::beginTransaction();

            if (empty($input['sort'])) {
                $input['sort'] = 100;
            }
            if (empty($input['hits'])) {
                $input['hits'] = rand(500, 1000);
            }

            if (empty($input['downloads'])) {
                $input['downloads'] = rand(100, 300);
            }

            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                throw new \Exception(trans('lang.add_error'));
            }

            $year = Products::publishedDateFormatYear($input['published_date']);
            if (!$year) {
                throw new \Exception(trans('lang.add_error') . ':published_date');
            }

            $productDescription = new ProductsDescription($year);
            $input['product_id'] = $record->id;
            $descriptionRecord = $productDescription->saveWithAttributes($input);
            if (!$descriptionRecord) {
                throw new \Exception(trans('lang.add_error'));
            }
            DB::commit();
            ReturnJson(TRUE, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            // 回滚事务
            // 建表时无法回滚
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 更新报告
     * @param $request 请求信息
     */
    protected function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            // 开启事务
            DB::beginTransaction();
            $model = $this->ModelInstance();
            $record = $model->findOrFail($input['id']);

            //旧纪录年份
            $oldYear = Products::publishedDateFormatYear($record->published_date);
            //新纪录年份
            $newYear = Products::publishedDateFormatYear($input['published_date']);
            // return $oldYear;
            if (empty($input['sort'])) {
                $input['sort'] = 100;
            }
            if (empty($input['hits'])) {
                $input['hits'] = rand(500, 1000);
            }

            if (empty($input['downloads'])) {
                $input['downloads'] = rand(100, 300);
            }

            if (!$record->update($input)) {
                throw new \Exception(trans('lang.update_error'));
            }

            $input['product_id'] = $record->id;
            $newProductDescription = (new ProductsDescription($newYear));
            //出版时间年份更改
            if ($oldYear != $newYear) {
                //删除旧详情
                if ($oldYear) {
                    $oldProductDescription = (new ProductsDescription($oldYear))->where('product_id', $record->id)->first();
                    $oldProductDescription->delete();
                }
                //然后新增
                $descriptionRecord = $newProductDescription->saveWithAttributes($input);
            } else {
                $newProductDescription = $newProductDescription->where('product_id', $record->id)->first();
                if ($newProductDescription) {
                    //直接更新
                    $descriptionRecord = $newProductDescription->updateWithAttributes($input);
                } else {
                    //不存在新增
                    $descriptionRecord = (new ProductsDescription($newYear))->saveWithAttributes($input);
                }
            }

            if (!$descriptionRecord) {
                throw new \Exception(trans('lang.update_error'));
            }

            DB::commit();
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * AJax单行删除
     * @param $ids 主键ID
     */
    protected function destroy(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            foreach ($ids as $id) {
                $record = $this->ModelInstance()->find($id);

                $year = Products::publishedDateFormatYear($record->published_date);
                if ($year) {
                    $recordDescription = (new ProductsDescription($year))->where('product_id', $record->id);
                }
                if ($record) {
                    $record->delete();
                }
                if ($recordDescription) {
                    $recordDescription->delete();
                }
            }
            ReturnJson(TRUE, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }



    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            //分类
            $data['category'] = (new ProductsCategory())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);
            //国家地区 region
            $data['country'] = (new Region())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);

            if ($request->HeaderLanguage == 'en') {
                $filed = ['english_name as label', 'value'];
            } else {
                $filed = ['name as label', 'value'];
            }
            //显示首页/热门/推荐
            $data['show_home'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Show_Home_State', 'status' => 1], ['sort' => 'ASC']);
            $data['show_hot'] = $data['show_home'];
            $data['show_recommend'] = $data['show_home'];
            $data['have_sample'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Has_Sample', 'status' => 1], ['sort' => 'ASC']);

            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);
            // 折扣
            $data['discount_type'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Discount_Type', 'status' => 1], ['sort' => 'ASC']);


            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 修改基础价
     * @param $request 请求信息
     * @param $id 主键ID
     */
    public function changePrice(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(FALSE, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->price = $request->price;
            if (!$record->save()) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 热门开关
     * @param $request 请求信息
     * @param $id 主键ID
     */
    public function changeHot(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(FALSE, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->show_hot = $request->show_hot;
            if (!$record->save()) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 精品开关
     * @param $request 请求信息
     * @param $id 主键ID
     */
    public function changeRecommend(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(FALSE, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->show_recommend = $request->show_recommend;
            if (!$record->save()) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 修改分类折扣
     * @param $request 请求信息
     * @param $id 主键ID
     */
    public function discount(Request $request)
    {

        try {
            if (empty($request->id)) {
                ReturnJson(FALSE, 'id is empty');
            }
            if (empty($request->discount_type)) {
                ReturnJson(FALSE, 'discount type is empty');
            }
            if (empty($request->discount_value)) {
                ReturnJson(FALSE, 'discount value is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);

            $type = $request->discount_type;
            $value = $request->discount_value;

            $record->discount_type = $type;
            if ($type == 1) {
                $record->discount = $value;
                $record->discount_amount = 0;
            } elseif ($type == 2) {
                $record->discount = 100;
                $record->discount_amount = $value;
            }
            // else {
            //     throw new \Exception(trans('lang.update_error') . ':discount_type is out of range');
            // }
            //可能恢复原价
            if ($type == 1 && $value == 100) {
                $record->discount_time_begin = null;
                $record->discount_time_end = null;
            } elseif ($type == 2 && $value == 0) {
                $record->discount_time_begin = null;
                $record->discount_time_end = null;
            } else {
                $record->discount_time_begin = $request->discount_time_begin;
                $record->discount_time_end = $request->discount_time_end;
            }
            //验证
            request()->offsetSet('discount', $record->discount);
            request()->offsetSet('discount_amount', $record->discount_amount);
            $this->ValidateInstance($request);

            if (!$record->save()) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 批量修改下拉参数
     * @param $request 请求信息
     */
    public function batchUpdateParam(Request $request)
    {
        $field = Products::getBatchUpdateField();
        array_unshift($field, ['name' => '请选择', 'value' => '', 'type' => '']);
        ReturnJson(TRUE, trans('lang.request_success'), $field);
    }


    /**
     * 批量修改下拉参数子项
     * @param $request 请求信息
     */
    public function batchUpdateOption(Request $request)
    {
        $input = $request->all();
        $keyword = $input['keyword'];
        $data = [];
        if ($keyword == 'category_id') {
            //分类
            $data = (new ProductsCategory())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);
        } elseif ($keyword == 'status') {
            if ($request->HeaderLanguage == 'en') {
                $filed = ['english_name as label', 'value'];
            } else {
                $filed = ['name as label', 'value'];
            }
            $data = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Show_Home_State', 'status' => 1], ['sort' => 'ASC']);
        }

        ReturnJson(TRUE, trans('lang.request_success'), $data);
    }

    /**
     * 批量修改
     * @param $request 请求信息
     */
    public function batchUpdate(Request $request)
    {

        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $keyword = $input['keyword'] ?? '';
        $value = $input['value'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作

        $ModelInstance = $this->ModelInstance();
        $model = $ModelInstance->query();

        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(TRUE, trans('lang.param_empty') . ':ids');
            }
            $model = $ModelInstance->whereIn('id', $ids);
        } else {
            //筛选
            $model = $ModelInstance->HandleWhere($model, $request);
        }
        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } else {
            $data['result_count'] = $model->update([$keyword => $value]);
            ReturnJson(TRUE, trans('lang.update_success'));
        }
    }



    /**
     * 批量上传报告
     * @param $request 请求信息
     */
    public function uploadProducts(Request $request)
    {
        $basePath = public_path() . '/site';
        $basePath .= '/' . $request->header('Site');

        $pathsStr = $request->path;
        //上传记录初始化
        $logModel = ProductsUploadLog::create([
            'file' => $pathsStr
        ]);

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
        foreach ($paths as $key => $value) {
            $path = $basePath . $value;
            $data = [
                'class' => 'Modules\Site\Http\Controllers\ProductsController',
                'method' => 'handleExcelFile',
                'site' => $request->header('Site') ?? '',
                'log_id' => $logModel->id,
                'data' => $path,
                'fieldData' => $fieldData,

            ];
            $data = json_encode($data);
            $RabbitMQ = new RabbitmqService();
            $RabbitMQ->setQueueName('products-file-queue'); // 设置队列名称
            $RabbitMQ->setExchangeName('Products'); // 设置交换机名称
            $RabbitMQ->setQueueMode('direct'); // 设置队列模式
            $RabbitMQ->setRoutingKey('productsKey1');
            $RabbitMQ->push($data); // 推送数据
        }

        ReturnJson(TRUE, trans('lang.request_success'));
    }

    /**
     * 批量上传报告/队列处理文件
     * @param $params 
     */
    public function handleExcelFile($params = null)
    {
        ini_set('memory_limit', '4096M');
        if (empty($params)) {
            throw new \Exception("filepath is empty", 1);
        }



        //读取文件
        $path = $params['data'];
        $fieldData = $params['fieldData'];
        $fieldSort = array_keys($fieldData);
        $reader = ReaderEntityFactory::createXLSXReader($path);
        $reader->setShouldPreserveEmptyRows(true);
        $reader->open($path);
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
                $tempRow =  $sheetRow->toArray();
                $row = [];
                foreach ($tempRow as $tempKey => $tempValue) {
                    if (in_array($tempKey, $fieldSort)) {
                        $row[$fieldData[$tempKey]] = $tempValue;
                    }
                }
                if(count($row)>0){
                    $excelData[] = $row;
                }
            }
        }

        //加入队列
        if ($excelData && count($excelData) > 0) {
            $groupData = array_chunk($excelData, 100);
            foreach ($groupData as $item) {

                $data = [
                    'class' => 'Modules\Site\Http\Controllers\ProductsController',
                    'method' => 'handleProducts',
                    'site' => $params['site'],
                    'log_id' => $params['log_id'],
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

        $count = 0;
        $insertCount = 0;
        $updateCount = 0;
        $errorCount = 0;
        $details = '';

        foreach ($params['data'] as $row) {
            $count++;
            try {
                //表头
                $item = [];
                isset($row['name']) && $item['name'] = $row['name'];
                isset($row['pages']) && $item['pages'] = $row['pages'];
                isset($row['tables']) && $item['tables'] = $row['tables'];
                isset($row['price']) && $item['price'] = $row['price'];
                isset($row['published_date']) && $item['published_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($row['published_date']);

                isset($row['category_id']) && $item['category_id'] = ProductsCategory::where('name', trim($row['category_id']))->value('id') ?? 0;

                isset($row['author']) && $item['author'] = $row['author'];
                isset($row['keywords']) && $item['keywords'] = $row['keywords'];


                $itemDescription = [];
                isset($row['description']) && $itemDescription['description'] = str_replace('_x000D_', '', $row['description']);
                isset($row['table_of_content']) && $itemDescription['table_of_content'] = str_replace('_x000D_', '', $row['table_of_content']);
                isset($row['tables_and_figures']) && $itemDescription['tables_and_figures'] = str_replace('_x000D_', '', $row['tables_and_figures']);
                isset($row['companies_mentioned']) && $itemDescription['companies_mentioned'] = str_replace('_x000D_', '', $row['companies_mentioned']);

                //新纪录年份
                $newYear = Products::publishedDateFormatYear($item['published_date']);

                // 处理每行数据
                $product = Products::where('name', trim($row['name']))->first();
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
                $details .= '【'.$row['name']??''.'】'.$th->getMessage() . "\r\n";
                // $details = $th->getLine().$th->getMessage().$th->getTraceAsString() . "\r\n";
                $errorCount++;
            }
        }
        try {
            DB::beginTransaction();
            $logModel = ProductsUploadLog::where(['id' => $params['log_id']])->first();
            $logData = [
                'count' => ($logModel->count ?? 0) + $count,
                'insert_count' => ($logModel->insert_count ?? 0) + $insertCount,
                'update_count' => ($logModel->update_count ?? 0) + $updateCount,
                'error_count' => ($logModel->error_count ?? 0) + $errorCount,
                'details' => ($logModel->details ?? '') . "\r\n" . $details,

            ];
            $logFlag = $logModel->update($logData);

            if ($logFlag) {
                DB::commit();
            } else {
                DB::rollBack();
                // 处理更新失败的情况
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // 处理异常，例如日志记录
            throw $e;
        }
    }
}
