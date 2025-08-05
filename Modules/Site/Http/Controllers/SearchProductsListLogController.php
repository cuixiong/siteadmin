<?php

namespace Modules\Site\Http\Controllers;

use App\Const\QueueConst;
use App\Jobs\ExportAccess;
use App\Jobs\ExportProduct;
use App\Jobs\HandlerExportExcel;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\AccessExportLog;
use Modules\Site\Http\Models\AccessLog;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\ProductsExcelField;
use Modules\Site\Http\Models\ProductsExportLog;
use Modules\Site\Http\Models\SearchProductsListLog;
use Modules\Site\Http\Models\ViewProductsExportLog;
use Modules\Site\Http\Models\ViewProductsLog;
use Modules\Site\Services\IPAddrService;

class SearchProductsListLogController extends CrudController
{

    public function searchDroplist(Request $request)
    {
        try {

            $timeConst = [
                'today'         => '今日',
                'last_week'     => '上周',
                'this_week'     => '本周',
                'last_month'    => '上月',
                'this_month'    => '当月',
                'all'           => '全部',
            ];
            $timeList = [];
            foreach ($timeConst as $key => $forTime) {
                $addData = [
                    'value' => $key,
                    'label' => $forTime
                ];
                $timeList[] = $addData;
            }
            $data['time_list'] = $timeList;

            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    // 根据时间类型返回起始和结束时间
    function getTimeRange($type)
    {
        // 设置一周从周一开始（默认是周日）
        Carbon::setWeekStartsAt(Carbon::MONDAY);

        $now = Carbon::now();
        if ($type == 'today') {
            return [
                'start' => $now->startOfDay()->getTimestamp(),
                'end'   => $now->copy()->endOfDay()->getTimestamp(),
            ];
        } elseif ($type == 'last_week') {
            return [
                'start' => $now->copy()->subWeek()->startOfWeek()->getTimestamp(),
                'end'   => $now->copy()->subWeek()->endOfWeek()->getTimestamp(),
            ];
        } elseif ($type == 'this_week') {
            return [
                'start' => $now->copy()->startOfWeek()->getTimestamp(),
                'end'   => $now->getTimestamp(),
            ];
        } elseif ($type == 'last_month') {
            return [
                'start' => $now->copy()->subMonth()->startOfMonth()->getTimestamp(),
                'end'   => $now->copy()->subMonth()->endOfMonth()->getTimestamp(),
            ];
        } elseif ($type == 'this_month') {
            return [
                'start' => $now->copy()->startOfMonth()->getTimestamp(),
                'end'   => $now->getTimestamp(),
            ];
        }

        return null;
    }

    /**
     * @param Request $request
     *
     */
    public function list(Request $request)
    {
        try {
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            //搜索条件
            if (isset($search['ip']) && !empty($search['ip'])) {
                $searchCondition['ip'] = $search['ip'];
            }
            if (isset($search['keywords']) && !empty($search['keywords'])) {
                $searchCondition['keywords'] = $search['keywords'];
            }

            //时间条件
            $droplistTimeType = $search['time_type'] ?? '';
            $start_time = $search['select_time_start'] ?? '';
            $end_time = $search['select_time_end'] ?? '';
            
            $tab = 'keywords';
            $data = [];
            if (!empty($start_time) && !empty($end_time)) {
                $startTimestamp = Carbon::parse($start_time)->getTimestamp();
                $endTimestamp = Carbon::parse($end_time)->getTimestamp();
                $data = $this->getCountInTable($startTimestamp, $endTimestamp, $tab, $searchCondition);
            } else {
                $droplistTime = $this->getTimeRange($droplistTimeType);
                // ReturnJson(true, trans('lang.request_success'), $droplistTime);
                if ($droplistTime) {
                    $startTimestamp = $droplistTime['start'];
                    $endTimestamp = $droplistTime['end'];
                    $data = $this->getCountInTable($startTimestamp, $endTimestamp, $tab, $searchCondition);
                } else {
                    $data = $this->getCountInTable(null, null, $tab, $searchCondition);
                }
            }

            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


    // 查询单表内的记录数
    public function getCountInTable($start_time, $end_time, $field = 'keywords', $condition = [])
    {
        $logModel = SearchProductsListLog::query();
        if ($condition && count($condition) > 0) {
            $logModel = $logModel->where($condition);
        }
        if ($start_time && $end_time) {
            $logModel = $logModel->whereBetween('created_at', [$start_time, $end_time]);
        }
        $logModel = $logModel->groupBy($field);
        // 数量
        $count = (clone $logModel)->selectRaw($field)->get()->count();
        $logModel->selectRaw($field . ', count(*) as count, max(created_at) as log_time ')->orderBy('count', 'desc');
        // 查询偏移量
        $request = request();
        $page = $request->page ?? 1;
        $pageSize = $request->pageSize ?? 20;
        if (!empty($page) && !empty($pageSize)) {
            $logModel->offset(($page - 1) * $pageSize)->limit($pageSize);
        }
        $list = $logModel->get()->toArray();

        foreach ($list as $key => &$value) {
            $value['log_time'] = date("Y-m-d H:i:s", $value['log_time']);
        }

        $data = [
            'count' => $count,
            'page' => $page,
            'pageSize' => $pageSize,
            'pageCount' => ceil($count / $pageSize),
            'list' => $list,
        ];
        return $data;
    }

    // 详情
    public function details(Request $request)
    {
        try {
            $keywords = $request->keywords;
            $data = SearchProductsListLog::query()
                ->select(['id', 'ip', 'ip_addr', 'keywords', 'created_at'])
                ->where('keywords', $keywords)
                ->orderBy('id', 'desc')
                ->get();
            if ($data) {
                $data = $data->toArray();
                foreach ($data as $key => $item) {
                    # code...
                }
            } else {
                $data = [];
            }
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 选中删除
     *
     * @param Request $request
     *
     */
    public function delete(Request $request)
    {
        try {
            $keywords = $request->input('keywords');
            $keywordsArray = @json_decode($keywords, true);
            if (empty($keywordsArray)) {
                ReturnJson(false, trans('未传入搜索词'));
            }

            $type = $request->input('type'); //1：获取数量;2：执行操作

            $query = SearchProductsListLog::query()->where('keywords', $keywords);
            if ($type == 1) {
                $recordCount = $query->count();
                $data = [
                    'keywordsCount' => count($keywordsArray),
                    'recordCount' => $recordCount ?? 0,
                ];
            } elseif ($type == 2) {
                $deleteCount = $query->delete();
                $data = [
                    'deleteCount' => $deleteCount ?? 0,
                ];
            }

            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


    /**
     * 批量删除
     */
    public function deleteBatch(Request $request)
    {
        try {
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            //搜索条件
            if (isset($search['ip']) && !empty($search['ip'])) {
                $searchCondition['ip'] = $search['ip'];
            }
            if (isset($search['keywords']) && !empty($search['keywords'])) {
                $searchCondition['keywords'] = $search['keywords'];
            }

            //时间条件
            $droplistTimeType = $search['time_type'] ?? '';
            $start_time = $search['select_time_start'] ?? '';
            $end_time = $search['select_time_end'] ?? '';
            $type = $request->input('type'); //1：获取数量;2：执行操作

            if (!empty($start_time) && !empty($end_time)) {
                $start_time = Carbon::parse($start_time)->getTimestamp();
                $end_time = Carbon::parse($end_time)->getTimestamp();
            } else {
                $droplistTime = $this->getTimeRange($droplistTimeType);
                // ReturnJson(true, trans('lang.request_success'), $droplistTime);
                if ($droplistTime) {
                    $start_time = $droplistTime['start'];
                    $end_time = $droplistTime['end'];
                }
            }
            
            $query = SearchProductsListLog::query();
            if ($searchCondition && count($searchCondition) > 0) {
                $query = $query->where($searchCondition);
            }
            if ($start_time && $end_time) {
                $query = $query->whereBetween('created_at', [$start_time, $end_time]);
            }
            
            if ($type == 1) {
                $keywordsCount = (clone $query)->groupBy('keywords')->count();
                $recordCount = $query->count();
                $data = [
                    'keywordsCount' => $keywordsCount??0,
                    'recordCount' => $recordCount ?? 0,
                ];
            } elseif ($type == 2) {
                $deleteCount = $query->delete();
                $data = [
                    'deleteCount' => $deleteCount ?? 0,
                ];
            }

            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function export(Request $request)
    {

    }

}
