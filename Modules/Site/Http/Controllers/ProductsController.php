<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Site\Http\Models\Prodcuts;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\ProductsDescription;
use Illuminate\Support\Facades\DB;
use App\Services\RabbitmqService;

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
                $record[$key]['description'] = $descriptionData['description'];
                $record[$key]['table_of_content'] = $descriptionData['table_of_content'];
                $record[$key]['tables_and_figures'] = $descriptionData['tables_and_figures'];
                $record[$key]['description_en'] = $descriptionData['description_en'];
                $record[$key]['table_of_content_en'] = $descriptionData['table_of_content_en'];
                $record[$key]['tables_and_figures_en'] = $descriptionData['tables_and_figures_en'];
                $record[$key]['companies_mentioned'] = $descriptionData['companies_mentioned'];
            }

            $data = [
                'total' => $total,
                'list' => $record
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

            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                throw new \Exception(trans('lang.add_error'));
            }

            $year = $this->publishedDateFormatYear($input['published_date']);
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
            $record = $model->findOrFail($request->id);

            //旧纪录年份
            $oldYear = $this->publishedDateFormatYear($record->published_date);
            //新纪录年份
            $newYear = $this->publishedDateFormatYear($input['published_date']);
            // return $oldYear;
            if (!$record->update($input)) {
                throw new \Exception(trans('lang.update_error'));
            }

            $input['product_id'] = $record->id;
            $newProductDescription = (new ProductsDescription($newYear));
            //出版时间年份更改
            if ($oldYear != $newYear) {
                //删除旧详情
                if ($oldYear) {
                    $oldProductDescription = (new ProductsDescription($oldYear))->where('product_id', $request->id)->first();
                    $oldProductDescription->delete();
                }
                //然后新增
                $descriptionRecord = $newProductDescription->saveWithAttributes($input);
            } else {
                //直接更新
                $newProductDescription = $newProductDescription->where('product_id', $request->id)->first();
                $descriptionRecord = $newProductDescription->updateWithAttributes($input);
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

    private function publishedDateFormatYear($timestamp)
    {

        $year = date('Y', $timestamp);
        if (empty($year) || !is_numeric($year) || strlen($year) !== 4) {
            return false;
        }
        return $year;
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

                $year = $this->publishedDateFormatYear($record->published_date);
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
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $filed = ['english_name as label', 'value'];
            } else {
                $filed = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);


            ReturnJson(TRUE, trans('lang.request_success'), $data);
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
     * 批量上传报告
     * @param $request 请求信息
     */
    public function uploadProducts(Request $request) {

        $paths = $request->path;
        // return $paths;
        //读取
        $data = ['id'=>112333,'name'=>'zqy'];
        $data = json_encode(['class' => 'Modules\Site\Http\Controllers\ProductsController', 'method' => 'handleProducts', 'data'=>$data]);
        $RabbitMQ = new RabbitmqService();
        
        $RabbitMQ->setQueueName('products-queue');// 设置队列名称
        $RabbitMQ->setExchangeName('Products');// 设置交换机名称
        $RabbitMQ->setQueueMode('fanout');// 设置队列模式
        $RabbitMQ->push($data);// 推送数据
        echo '推送成功';
    }

    /**
     * 批量上传报告
     * @param $params 报告数据
     */
    public function handleProducts($params = null) {
        // file_put_contents("C:\\Users\\Administrator\\Desktop\\aaaaaa.txt",json_encode($params));
    }
}
