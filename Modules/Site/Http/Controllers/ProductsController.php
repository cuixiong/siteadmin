<?php

namespace Modules\Site\Http\Controllers;

use App\Exports\ProductsExport;
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
                $model = $model->orderBy('sort', $sort)->orderBy('id', 'DESC');
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
     * 批量删除
     * @param $request 请求信息
     */
    public function batchDelete(Request $request)
    {

        $input = $request->all();
        $ids = $input['ids'] ?? '';
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
            $data['result_count'] = $model->delete();
            ReturnJson(TRUE, trans('lang.delete_success'));
        }
    }


    /**
     * 批量导出
     * @param $request 请求信息
     */
    public function export(Request $request)
    {

        // return Excel::download(new ProductsExport, 'products.xlsx');

        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作

        $ModelInstance = $this->ModelInstance();
        $model = $ModelInstance->query();

        // if ($ids) {
        //     //选中
        //     $ids = explode(',', $ids);
        //     if (!(count($ids) > 0)) {
        //         ReturnJson(TRUE, trans('lang.param_empty') . ':ids');
        //     }
        //     $model = $ModelInstance->whereIn('id', $ids);
        // } else {
        //     //筛选
        //     $model = $ModelInstance->HandleWhere($model, $request);
        // }
        // $data = [];
        // if ($type == 1) {
        //     // 总数量
        //     $data['count'] = $model->count();
        //     ReturnJson(TRUE, trans('lang.request_success'), $data);
        // } else {
        // $data['result_count'] = $model->delete();
        //查询出涉及的id，并分割加入队列
        $idsData = $model->select('id')->pluck('id')->toArray();
        if (!(count($idsData) > 0)) {
            ReturnJson(TRUE, trans('lang.data_empty'));
        }

        //加入队列
        $dirName = time() . rand(10000, 99999);
        $dirPath = 'C:\\Users\\Administrator\\Desktop\\zqy\\' . $dirName;
        //创建目录
        if (!is_dir($dirPath)) {
            @mkdir($dirPath, 0777, true);
        }
        $groupData = array_chunk($idsData, 100);
        foreach ($groupData as $key => $item) {

            $data = [
                'class' => 'Modules\Site\Http\Controllers\ProductsController',
                'method' => 'handleExport',
                'site' => $request->header('Site') ?? '',   //站点名称
                'data' => $item,    //传递文件路径
                'dirPath' => $dirPath,
                'chip' => $key,
                // 'log_id' => $logModel->id,  //写入日志的id
                // 'fieldData' => $fieldData,  //字段与excel表头的对应关系
                // 'pulisher_id' => $pulisher_id,  //出版商id
            ];
            $data = json_encode($data);
            $RabbitMQ = new RabbitmqService();
            $RabbitMQ->setQueueName('products-export'); // 设置队列名称
            $RabbitMQ->setExchangeName('ProductsExport'); // 设置交换机名称
            $RabbitMQ->setQueueMode('direct'); // 设置队列模式
            $RabbitMQ->push($data); // 推送数据
        }

        ReturnJson(TRUE, trans('lang.request_success'));
        // }
    }

    /**
     * 批量导出分块文件
     * @param $params 
     */
    public function handleExport($params = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        if (empty($params['site'])) {
            throw new \Exception("site is empty", 1);
        }
        // 设置当前租户
        tenancy()->initialize($params['site']);

        try {
            //读取数据
            $record = Products::select(['id', 'name', 'published_date'])->whereIn('id', $params['data'])->get()->makeHidden((new Products())->getAppends())->toArray();

            $dirPath = $params['dirPath'];
            $chip = $params['chip'];
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($dirPath . '/' . $chip . '.xlsx');
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


                $rowFromValues = WriterEntityFactory::createRowFromArray($record[$key]);
                $writer->addRow($rowFromValues);
            }
            // $writer->addRows($record);
            $writer->close();
            // $title = array_keys($data[0]);
            //code...
        } catch (\Throwable $th) {
            // file_put_contents('C:\\Users\\Administrator\\Desktop\\123.txt', $th->getMessage(), FILE_APPEND);
        }
        if ($chip == 2) {

            $data = [
                'class' => 'Modules\Site\Http\Controllers\ProductsController',
                'method' => 'handleMergeFile',
                'data' => $dirPath,
                // 'log_id' => $logModel->id,  //写入日志的id
                // 'fieldData' => $fieldData,  //字段与excel表头的对应关系
                // 'pulisher_id' => $pulisher_id,  //出版商id
            ];
            $data = json_encode($data);
            $RabbitMQ = new RabbitmqService();
            $RabbitMQ->setQueueName('products-export'); // 设置队列名称
            $RabbitMQ->setExchangeName('ProductsExport'); // 设置交换机名称
            $RabbitMQ->setQueueMode('direct'); // 设置队列模式
            $RabbitMQ->push($data); // 推送数据
        }
    }


    /**
     * 批量导出合并文件
     * @param $params 
     */
    public function handleMergeFile($params = null)
    {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');

            $existingFilePath = ['0.xlsx', '1.xlsx', '2.xlsx'];
            $dirPath = $params['data'];

            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($dirPath . '.xlsx');
            $style = (new StyleBuilder())->setShouldWrapText(false)->build();

            foreach ($existingFilePath as $key => $path) {
                // we need a reader to read the existing file...
                $reader = ReaderEntityFactory::createXLSXReader();
                $reader->setShouldPreserveEmptyRows(true);
                $reader->open($dirPath . '/' . $path);

                // let's read the entire spreadsheet...
                foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
                    // Add sheets in the new file, as we read new sheets in the existing one

                    foreach ($sheet->getRowIterator() as $row) {
                        // ... and copy each row into the new spreadsheet
                        $row = WriterEntityFactory::createRowFromArray($row->toArray(), $style);
                        $writer->addRow($row);
                    }
                }


                $reader->close();
            }
            $writer->close();
        } catch (\Throwable $th) {
            // file_put_contents('C:\\Users\\Administrator\\Desktop\\123.txt', $th->getTraceAsString(), FILE_APPEND);
        }
    }
}
