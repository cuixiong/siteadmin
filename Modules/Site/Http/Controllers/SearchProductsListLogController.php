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

class SearchProductsListLogController extends CrudController {
    public $sumCnt = 0;
    public $total  = 0;

    public function searchDroplist(Request $request) {
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
                'start' => $now->startOfDay(),
                'end'   => $now->copy()->endOfDay(),
            ];
        } elseif ($type == 'last_week') {
            return [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end'   => $now->copy()->subWeek()->endOfWeek(),
            ];
        } elseif ($type == 'this_week') {
            return [
                'start' => $now->copy()->startOfWeek(),
                'end'   => $now,
            ];
        } elseif ($type == 'last_month') {
            return [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end'   => $now->copy()->subMonth()->endOfMonth(),
            ];
        } elseif ($type == 'this_month') {
            return [
                'start' => $now->copy()->startOfMonth(),
                'end'   => $now,
            ];
        }

        return null;
    }

    /**
     * @param Request $request
     *
     */
    public function ReportForms(Request $request) {
        try {
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            //搜索条件
            if (!empty($search)) {
                foreach ($search as $key => $value) {
                    if ($key == 'ip' && !empty($value)) {
                        $searchCondition['ip'] = $value;
                    } elseif ($key == 'keywords' && !empty($value)) {
                        $searchCondition['keywords'] = $value;
                    }
                }
            }
            
            //时间条件
            $droplistTimeType = $search['time'] ?? '';
            $selectTime = $search['selectTime'] ?? '';
            if (!empty($selectTime)) {
                $start_time = $selectTime[0] ?? '';
                $end_time = $selectTime[1] ?? '';
            }

            $tab = 'keywords';
            $list = [];
            if (!empty($start_time) && !empty($end_time)) {
                $srartTimeCoon = Carbon::parse($start_time);
                $endTimeCoon = Carbon::parse($end_time);
                $list = $this->getCountInTable($srartTimeCoon, $endTimeCoon, $tab, $searchCondition);
            } else {
                $droplistTime = $this->getTimeRange($droplistTimeType);
                if($droplistTime){
                    $srartTimeCoon = $droplistTime['start'];
                    $endTimeCoon = $droplistTime['end'];
                    $list = $this->getCountInTable($srartTimeCoon, $endTimeCoon, $tab, $searchCondition);
                }else{

                }
            }
            $data['total'] = $this->total;
            $data['list'] = $list;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


    // 查询单表内的记录数
    public function getCountInTable(Carbon $start, Carbon $end, $field = 'keywords', $condition = []) {
        $logModel = new SearchProductsListLog();
        $logModel = $logModel::query()->where($condition);
        if ($start && $end) {
            $start_time = $start->getTimestamp();
            $end_time = $end->getTimestamp();
            $logModel->whereBetween('created_at', [$start_time, $end_time]);
        } else {
        }
        $list = $logModel->groupBy($field);
        // 总数据数量
        $count = $list->selectRaw($field)->get()->count();
        $this->total = $count;
        // 分组下数量
        $sumCnt = $list->selectRaw($field." , count(*) as cnt ")->get()->sum('cnt');
        $this->sumCnt = $sumCnt;
        
        $list = $list->selectRaw($field.' as targetField , count(*) as count, max(created_at) as log_time ')->orderBy('count', 'desc');
        // 查询偏移量
        $request = request();
        if (!empty($request->pageNum) && !empty($request->pageSize)) {
            $list = $list->offset(($request->pageNum - 1) * $request->pageSize)->limit($request->pageSize);
        }
        $list = $list->get()->toArray();
        foreach ($list as $key => &$value) {
            $value['ip_addr'] = (new IPAddrService($value['ip_refer']))->getAddrStrByIp();
            $value['log_time'] = date("Y-m-d H:i:s", $value['log_time']);
        }

        return $list;
    }

    /**
     * 根据当前条件, 当前ip/引用头 获取访问详情
     *
     * @param Request $request
     *
     */
    public function accessDetailList(Request $request) {
        try {
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            //搜索条件
            if (!empty($search)) {
                foreach ($search as $key => $value) {
                    if ($key == 'ip' && !empty($value)) {
                        $ipList = explode(".", $value);
                        $ipCnt = count($ipList);
                        if ($ipCnt == 2) {
                            $searchCondition['ip_muti_second'] = $value;
                        } elseif ($ipCnt == 3) {
                            $searchCondition['ip_muti_third'] = $value;
                        } else {
                            $searchCondition['ip'] = $value;
                        }
                    } elseif ($key == 'ip_addr' && !empty($value)) {
                        $searchCondition['ip_addr'] = $value;
                    } elseif ($key == 'referer') {
                        $searchCondition['referer'] = $value;
                    } elseif ($key == 'ua_info' && !empty($value)) {
                        $searchCondition['ua_info'] = $value;
                    }
                }
            }
            //时间条件
            $time = $search['time'] ?? '';
            $selectTime = $search['SelectTime'] ?? '';
            if (!empty($selectTime)) {
                $start_time = $selectTime[0] ?? '';
                $end_time = $selectTime[1] ?? '';
            }
            if (!empty($start_time) && !empty($end_time)) {
                $srartTimeCoon = Carbon::parse($start_time);
                $endTimeCoon = Carbon::parse($end_time);
            } else {
                $endTimeCoon = Carbon::now();
                if ($time == '1m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                } else if ($time == '5m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(5);
                } elseif ($time == '1h') {
                    $srartTimeCoon = Carbon::now()->subHour();
                } elseif ($time == 'today') {
                    $srartTimeCoon = Carbon::today();
                } elseif ($time == 'yesterday') {
                    $srartTimeCoon = Carbon::yesterday();
                } elseif ($time == '7d') {
                    $srartTimeCoon = Carbon::now()->subDays(7);
                } elseif ($time == '30d') {
                    $srartTimeCoon = Carbon::now()->subDays(30);
                } else {
                    //默认1分钟
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                }
            }
            //$searchCondition
            $list = $this->getAcrossTablesDetail($srartTimeCoon, $endTimeCoon, $searchCondition);
            $sumContentSize = array_column($list , 'content_size');
            $sumContentSize = array_sum($sumContentSize);
            $data['sum_size'] = $this->formatBytes($sumContentSize);
            $data['list'] = $list;
            $data['count'] = count($list);
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function getAcrossTablesDetail(Carbon $start, Carbon $end, $condition = []) {
        //直接调用
        $accessLogModel = new AccessLog();
        $list_all = $accessLogModel::query()
                                   ->where($condition)
                                   ->where('created_at', ">=", $start->getTimestamp())
                                   ->where('created_at', "<", $end->getTimestamp())
                                   ->orderBy('log_time', 'desc')
                                   ->get()->toArray();

        return $list_all;
    }

    /**
     * 根据条件去筛选 拷贝ip
     *
     * @param Request $request
     *
     */
    public function copyField(Request $request) {
        try {
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            //搜索条件
            if (!empty($search)) {
                foreach ($search as $key => $value) {
                    if ($key == 'ip' && !empty($value)) {
                        $ipList = explode(".", $value);
                        $ipCnt = count($ipList);
                        if ($ipCnt == 2) {
                            $searchCondition['ip_muti_second'] = $value;
                        } elseif ($ipCnt == 3) {
                            $searchCondition['ip_muti_third'] = $value;
                        } else {
                            $searchCondition['ip'] = $value;
                        }
                    } elseif ($key == 'ip_addr' && !empty($value)) {
                        $searchCondition['ip_addr'] = $value;
                    } elseif ($key == 'referer' && !empty($value)) {
                        $searchCondition['referer'] = $value;
                    } elseif ($key == 'ua_info' && !empty($value)) {
                        $searchCondition['ua_info'] = $value;
                    }
                }
            }
            //时间条件
            $time = $search['time'] ?? '';
            $selectTime = $search['SelectTime'] ?? '';
            if (!empty($selectTime)) {
                $start_time = $selectTime[0] ?? '';
                $end_time = $selectTime[1] ?? '';
            }
            if (!empty($start_time) && !empty($end_time)) {
                $srartTimeCoon = Carbon::parse($start_time);
                $endTimeCoon = Carbon::parse($end_time);
            } else {
                $endTimeCoon = Carbon::now();
                if ($time == '1m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                } else if ($time == '5m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(5);
                } elseif ($time == '1h') {
                    $srartTimeCoon = Carbon::now()->subHour();
                } elseif ($time == 'today') {
                    $srartTimeCoon = Carbon::today();
                } elseif ($time == 'yesterday') {
                    $srartTimeCoon = Carbon::yesterday();
                } elseif ($time == '7d') {
                    $srartTimeCoon = Carbon::now()->subDays(7);
                } elseif ($time == '30d') {
                    $srartTimeCoon = Carbon::now()->subDays(30);
                } else {
                    //默认1分钟
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                }
            }
            $start_time = $srartTimeCoon->getTimestamp();
            $end_time = $endTimeCoon->getTimestamp();
            //$searchCondition
            $ip_muti_str = $search['ip_muti_str'] ?? 'ip_muti_second';
            if ($ip_muti_str == 'ip_muti_second') {
                $field = 'ip_muti_second';
            } elseif ($ip_muti_str == 'ip_muti_third') {
                $field = 'ip_muti_third';
            } else {
                $field = 'ip';
            }
            $accessLogModel = new AccessLog();
            $list = $accessLogModel::query()
                                   ->where($searchCondition)
                                   ->whereBetween('created_at', [$start_time, $end_time])
                                   ->groupBy($field)
                                   ->selectRaw($field." as target_field , count(*) as count ")
                                   ->orderBy('count', 'desc')
                                   ->get()->toArray();
            $ipList = [];
            foreach ($list as $info) {
                if ($ip_muti_str == 'ip_muti_second') {
                    $ipstr = 'deny '.$info['target_field'].'.0.0/16';
                } elseif ($ip_muti_str == 'ip_muti_third') {
                    $ipstr = 'deny '.$info['target_field'].'.0/24';
                } else {
                    $ipstr = 'deny '.$info['target_field'];
                }
                $ipList[] = $ipstr;
            }
            $data['list'] = $ipList;
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
    public function accessLogDel(Request $request) {
        try {
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $ip_muti_str = $search['ip_muti_str'] ?? 'ip_muti_second';
            $searchCondition = [];
            //搜索条件
            $accessLogModel = new AccessLog();
            if (!empty($search)) {
                foreach ($search as $key => $value) {
                    if ($key == 'ip' && is_array($value)) {
                        if ($ip_muti_str == 'ip_muti_second') {
                            $searchCondition[] = ['ip_muti_second', 'in', $value];
                            $accessLogModel = $accessLogModel->whereIn("ip_muti_second", $value);
                        } elseif ($ip_muti_str == 'ip_muti_third') {
                            $searchCondition[] = ['ip_muti_third', 'in', $value];
                            $accessLogModel = $accessLogModel->whereIn("ip_muti_third", $value);
                        } else {
                            $searchCondition[] = ['ip', 'in', $value];
                            $accessLogModel = $accessLogModel->whereIn("ip", $value);
                        }
                    } elseif ($key == 'referer' && is_array($value)) {
                        $searchCondition['referer'] = $value;
                        $accessLogModel = $accessLogModel->whereIn("referer", $value);
                    } elseif ($key == 'ua_info' && is_array($value)) {
                        $searchCondition['ua_info'] = $value;
                        $accessLogModel = $accessLogModel->whereIn("ua_info", $value);
                    }
                }
            }
            if (empty($searchCondition)) {
                ReturnJson(false, trans('请选中条件'));
            }
            //时间条件
            $time = $search['time'] ?? '';
            $selectTime = $search['selectTime'] ?? '';
            if (!empty($selectTime)) {
                $start_time = $selectTime[0] ?? '';
                $end_time = $selectTime[1] ?? '';
            }
            if (!empty($start_time) && !empty($end_time)) {
                $srartTimeCoon = Carbon::parse($start_time);
                $endTimeCoon = Carbon::parse($end_time);
            } else {
                $endTimeCoon = Carbon::now();
                if ($time == '1m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                } elseif ($time == '5m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(5);
                } elseif ($time == '1h') {
                    $srartTimeCoon = Carbon::now()->subHour();
                } elseif ($time == 'today') {
                    $srartTimeCoon = Carbon::today();
                } elseif ($time == 'yesterday') {
                    $srartTimeCoon = Carbon::yesterday();
                } elseif ($time == '7d') {
                    $srartTimeCoon = Carbon::now()->subDays(7);
                } elseif ($time == '30d') {
                    $srartTimeCoon = Carbon::now()->subDays(30);
                } else {
                    //默认1分钟
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                }
            }
            //$searchCondition
            $start_time = $srartTimeCoon->getTimestamp();
            $end_time = $endTimeCoon->getTimestamp();
            $count = $accessLogModel->whereBetween('created_at', [$start_time, $end_time])->count();
            if ($count > 0) {
                $accessLogModel->whereBetween('created_at', [$start_time, $end_time])->delete();
            }
            $data['count'] = $count;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


}
