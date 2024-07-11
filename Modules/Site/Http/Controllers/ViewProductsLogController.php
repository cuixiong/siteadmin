<?php

namespace Modules\Site\Http\Controllers;

use App\Const\QueueConst;
use App\Jobs\ExportJob;
use App\Jobs\ExportProduct;
use App\Jobs\HandlerExportExcel;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\ProductsExcelField;
use Modules\Site\Http\Models\ProductsExportLog;
use Modules\Site\Http\Models\ViewProductsExportLog;
use Modules\Site\Http\Models\ViewProductsLog;

class ViewProductsLogController extends CrudController {
    public function options(Request $request) {
        $options = [];
        $codes = ['Switch_State'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select('code', 'value', $NameField)
                               ->orderBy('sort', 'asc')->get()->toArray();
        if (!empty($data)) {
            foreach ($data as $map) {
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        $options['country'] = Country::where('status', 1)->select('id as value', $NameField)->orderBy('sort', 'asc')
                                     ->get()->toArray();
        ReturnJson(true, '', $options);
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
            $record = $model->get()->toArray();
            $productIdList = array_column($record, 'product_id');
            $productList = Products::query()->whereIn("id", $productIdList)->select('id', 'name', 'url')
                                   ->get()->keyBy("id")->toArray();
            foreach ($record as $key => &$map) {
                $productId = $map['product_id'] ?? 0;
                if (!empty($productList[$productId])) {
                    $map['product_info'] = $productList[$productId];
                    $map['product_info']['report_url'] = getReportUrl($productList[$productId]);
                }
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
            $productInfo = Products::query()->select(["id", "name", "url"])->find($record->product_id);
            $productInfo['report_url'] = getReportUrl($productInfo);
            $record['product_info'] = $productInfo;
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 批量导出
     *
     * @param $request 请求信息
     */
    public function export(Request $request) {
        try {
            list($model, $count) = $this->getExportData($request);
            //返回条数
            if ($request->type == 1) {
                ReturnJson(true, trans('lang.request_success'), ['count' => $count]);
            }
            //定义导出目录
            $basePath = public_path();
            $site = getSiteName();
            if (empty($site)) {
                ReturnJson(false, trans('lang.site_not_exist'));
            }
            $dirMiddlePath = '/site/'.$site.'/exportDir/';
            $dirPath = $basePath.$dirMiddlePath;
            if (!is_dir($dirPath)) {
                @mkdir($dirPath, 0777, true);
            }
            //定义导出文件名
            $dirName = "view_products_export_".time();
            $filePath = $dirMiddlePath.$dirName.'.xlsx';
            //生成导出日志
            $addLog = [
                'file'  => $filePath,
                'count' => $count,
            ];
            $logModel = ViewProductsExportLog::create($addLog);
            $isQueue = true;
            if ($isQueue) {
                $data = [
                    'class'  => 'Modules\Site\Http\Controllers\ViewProductsLogController',
                    'method' => 'handleExportExcel',
                    'site'   => $site,   //站点名称
                    'reqinput'  => $request->input(),    //model 实例
                    'log_id' => $logModel->id,  //写入日志的id
                ];
                $data = json_encode($data);
                ExportJob::dispatch($data)->onQueue(QueueConst::QUEEU_EXPORT_VIEW_GOODS);
            } else {
                $data = [
                    'class'  => 'Modules\Site\Http\Controllers\ViewProductsLogController',
                    'method' => 'handleExportExcel',
                    'site'   => $site,   //站点名称
                    'reqinput'  => $request->input(),    //model 实例
                    'log_id' => $logModel->id,  //写入日志的id
                ];
                $this->handleExportExcel($data);;
            }
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
        ReturnJson(true, trans('lang.request_success'), $logModel->id);
    }

    /**
     *
     * @param 请求信息|Request $request
     *
     * @return array
     */
    private function getExportData(Request $request) {
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $ModelInstance = $this->ModelInstance();
        $model = $ModelInstance->query();
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty').':ids');
            }
            $model = $ModelInstance->whereIn('id', $ids);
        } else {
            //筛选
            $model = $ModelInstance->HandleWhere($model, $request);
        }
        $count = $model->count();
        if (empty($count)) {
            ReturnJson(true, trans('lang.data_empty'));
        }

        return [$model, $count];
    }

    public function handleExportExcel($params) {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        if (empty($params['site'])) {
            throw new \Exception("site is empty", 1);
        }
        // 设置当前租户
        tenancy()->initialize($params['site']);
        $exportLogInfo = ViewProductsExportLog::find($params['log_id']);
        $reqinput = $params['reqinput'];

        try {
            //读取数据
            $model = new ViewProductsLog();
            if(!empty($reqinput['ids'] )){
                $idList = explode("," , $reqinput['ids']);
                $model = $model->whereIn('id', $idList);
            }elseif(!empty($reqinput['search'] )){
                $model = $model->HandleSearch($model,$reqinput['search']);
            }
            $record = $model->get()->toArray();
            $productIdList = array_column($record, 'product_id');
            $productList = Products::query()->whereIn("id", $productIdList)->pluck('url', 'id')->toArray();
            foreach ($record as $key => &$map) {
                $productId = $map['product_id'] ?? 0;
                $map['report_url'] = '';
                if (!empty($productList[$productId])) {
                    $map['report_url'] = $productList[$productId];
                }
            }

            $writer = WriterEntityFactory::createXLSXWriter();
            $filename = public_path().$exportLogInfo['file'];
            if (!file_exists($filename)) {
                file_put_contents($filename, '');
            }
            $writer->openToFile($filename);
            $style = (new StyleBuilder())->setShouldWrapText(false)->build();
            //写入标题
            $title = [
                '编号',
                '用户id',
                '用户昵称',
                '报告id',
                '报告昵称',
                '报告关键词',
                '报告链接',
                'ip地址',
                'ip所在地',
                '当天浏览次数',
                '时间',
            ];
            $row = WriterEntityFactory::createRowFromArray($title, $style);
            $writer->addRow($row);
            foreach ($record as $key => $item) {
                $row = [];
                $row[] = $item['id'];
                $row[] = $item['user_id'];
                $row[] = $item['username'];
                $row[] = $item['product_id'];
                $row[] = $item['product_name'];
                $row[] = $item['keyword'];
                $row[] = $item['report_url'];
                $row[] = $item['ip'];
                $row[] = $item['ip_addr'];
                $row[] = $item['view_cnt'];
                $row[] = $item['created_at'];
                $rowFromValues = WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }
            // $writer->addRows($record);
            $writer->close();

        } catch (\Exception $th) {
            $details = $th->getMessage();
            throw $th;
        }

        //记录任务状态
        $logModel = ViewProductsExportLog::where(['id' => $params['log_id']])->first();
        $logData = [
            'state' => ViewProductsExportLog::EXPORT_COMPLETE,
        ];
        $logData['success_count'] = count($record);
        $logModel->update($logData);
        return true;

    }
}
