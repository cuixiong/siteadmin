<?php
/**
 * AccessLogController.php UTF-8
 * 访问日志报表
 *
 * @date    : 2024/10/31 9:52 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

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
use Modules\Site\Http\Models\ViewProductsExportLog;
use Modules\Site\Http\Models\ViewProductsLog;
use Modules\Site\Services\IPAddrService;

class AccessLogController extends CrudController {
    public $sumCnt = 0;
    public $total  = 0;

    public function searchDroplist(Request $request) {
        try {
            $ip_muti_list_str = [
                'ip_muti_second' => '*.*.0.0/16(二段)',
                'ip_muti_third'  => '*.*.*.0/24(三段)',
                'ip_muti_full'   => '*.*.*.*(完整IP)',
            ];
            foreach ($ip_muti_list_str as $key => $forTime) {
                $addData = [
                    'value' => $key,
                    'label' => $forTime
                ];
                $ip_muti_list[] = $addData;
            }
            $data['ip_muti_list'] = $ip_muti_list;
            $timeConst = [
                '1m'        => '1分钟',
                '5m'        => '5分钟',
                '1h'        => '1小时',
                'today'     => '今日',
                'yesterday' => '昨日',
                '7d'        => '7天',
                '30d'       => '30天',
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

    /**
     * 三个报表
     *
     * @param Request $request
     *
     */
    public function ReportForms(Request $request) {
        //5分钟 , 今日, 昨日, 近一小时, 近七天, 近30天
        try {
            //显示IP ,  归属地 ,  请求数
            $now = Carbon::now();
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $is_delete = $request->input('is_delete', 0);
            $searchCondition = [];
            //选项页
            $tab = $request->input('tab', 'ip'); //ip , referer , ua_info
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
            //多段IP
            if ($tab == 'ip') {
                $ip_muti_str = $search['ip_muti_str'] ?? 'ip_muti_second';
                if ($ip_muti_str == 'ip_muti_second') {
                    $tab = 'ip_muti_second';
                } elseif ($ip_muti_str == 'ip_muti_third') {
                    $tab = 'ip_muti_third';
                } else {
                    $tab = 'ip';
                }
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
                $list = $this->getCountInTable($srartTimeCoon, $endTimeCoon, $tab, $searchCondition, $is_delete);
            } else {
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
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                }
                $list = $this->getCountInTable($srartTimeCoon, $now, $tab, $searchCondition, $is_delete);
            }
            $data['total'] = $this->total;
            $data['list'] = $list;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function converIp($ban_ip_level, $ip) {
        $afterIp = explode(".", $ip);
        if (in_array($ban_ip_level, [1, 2, 3])) {
            //获取当前ip的请求次数
            for ($i = 4 - $ban_ip_level; $i < 4; $i++) {
                $afterIp[$i] = '*';
            }
            $ip = implode('.', $afterIp);
        }

        return $ip;
    }

    // 查询单表内的记录数
    public function getCountInTable(Carbon $start, Carbon $end, $field = 'ip', $condition = [], $is_delete = false) {
        if ($is_delete) {
            return $this->delAccessData($start, $end, $condition);
        }
        $accessLogModel = new AccessLog();
        $start_time = $start->getTimestamp();
        $end_time = $end->getTimestamp();
        $list = $accessLogModel::query()
                               ->where($condition)
                               ->whereBetween('created_at', [$start_time, $end_time])
                               ->groupBy($field);
        //获取总数量
        $count = $list->selectRaw($field)->get()->count();
        $this->total = $count;
        //获取总请求数
        $sumCnt = $list->selectRaw($field." , count(*) as cnt ")->get()->sum('cnt');
        $list = $list->orderBy('count', 'desc')
                     ->selectRaw(
                         $field
                         .' as targetField , count(*) as count, max(route) as route , max(log_time) as log_time , max(ip) as ip_refer , sum(content_size) as content_size '
                     );
        // 查询偏移量
        $request = request();
        if (!empty($request->pageNum) && !empty($request->pageSize)) {
            $list = $list->offset(($request->pageNum - 1) * $request->pageSize)->limit($request->pageSize);
        }
        $list = $list->get()->toArray();
        foreach ($list as $key => &$value) {
            $value['percent'] = round($value['count'] / $sumCnt * 100, 2);
            $value['ip_addr'] = (new IPAddrService($value['ip_refer']))->getAddrStrByIp();
            $value['log_time'] = date("Y-m-d H:i:s", $value['log_time']);

            $value['content_size'] = $this->formatBytes($value['content_size']);
        }

        return $list;
    }

    // 查询单表内的记录数
    public function delAccessData(Carbon $start, Carbon $end, $condition = []) {
        $date = $start->format('Ym');
        $accessLogModel = new AccessLog();
        $start_time = $start->getTimestamp();
        $end_time = $end->getTimestamp();
        $cnt = $accessLogModel::query()
                              ->where($condition)
                              ->whereBetween('created_at', [$start_time, $end_time])
                              ->count();
        $accessLogModel::query()->where($condition)
                       ->whereBetween('created_at', [$start_time, $end_time])
                       ->delete();

        return $cnt;
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
     * 根据条件去筛选 拷贝ip
     *
     * @param Request $request
     *
     */
    public function copyUaField(Request $request) {
        try {
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            //搜索条件
            if (!empty($search)) {
                foreach ($search as $key => $value) {
                    if ($key == 'referer' && !empty($value)) {
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

            $field = 'ua_info';
            $accessLogModel = new AccessLog();
            $list = $accessLogModel::query()
                                   ->where($searchCondition)
                                   ->whereBetween('created_at', [$start_time, $end_time])
                                   ->groupBy($field)
                                   ->selectRaw($field." as target_field , count(*) as count ")
                                   ->orderBy('count', 'desc')
                                   ->get()->toArray();
            $handlerFieldList = [];
            foreach ($list as $key => $value){
                $handlerFieldStr = $this->customEscape($value['target_field'], ['.' , '(' , ')' , '+' ,'?' , "*" , '\\']);
                $handlerFieldList[] = $handlerFieldStr;
            }

            $data = [
                'list'  => $handlerFieldList
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function customEscape($input, $characters) {
        // 转义指定的字符
        $escaped = '';
        foreach (str_split($input) as $char) {
            if (in_array($char, $characters)) {
                $escaped .= '\\' . $char; // 添加单个反斜杠
            } else {
                $escaped .= $char;
            }
        }
        return $escaped;
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

    public function accessLogExport(Request $request) {
        //默认条件筛选
        $search_type = $request->input('search_type', 1);
        $type = $request->input('type', 1);
        if ($search_type == 1) {
            $conditionModel = $this->getSearchCondition($request);
        } else {
            $conditionModel = $this->getSelectCondition($request);
        }
        $data = [];
        if ($type == 1) {
            // 总数量
            $cnt = $this->getCntByCondition($conditionModel);
            $data['count'] = $cnt;
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            $ids = $conditionModel->pluck('id')->toArray();
            if (empty($ids)) {
                ReturnJson(true, trans('lang.data_empty'));
            }
            //加入队列
            $basePath = public_path();
            $dirMiddlePath = '/site/'.$request->header('Site').'/exportDir/';
            //检验目录是否存在
            if (!is_dir($basePath.$dirMiddlePath)) {
                @mkdir($basePath.$dirMiddlePath, 0777, true);
            }
            //定义导出文件名
            $dirName = "access_log_export_".time();
            $filePath = $dirMiddlePath.$dirName.'.xlsx';
            //导出记录初始化,每个文件单独一条记录
            $logModel = AccessExportLog::create([
                                                    'file'  => $filePath,
                                                    'count' => count($ids),
                                                ]);
            $isQueue = false;
            $data = [
                'class'  => 'Modules\Site\Http\Controllers\AccessLogController',
                'method' => 'handleExportExcel',
                'site'   => $request->header('Site') ?? '',   //站点名称
                'data'   => $ids,    //要导出的报告id数据
                'log_id' => $logModel->id,  //写入日志的id
            ];
            if ($isQueue) {
                $data = json_encode($data);
                ExportAccess::dispatch($data)->onQueue(QueueConst::QUEEU_EXPORT_ACCESS_LOG);
            } else {
                $this->handleExportExcel($data);
            }
            ReturnJson(true, trans('lang.request_success'), $logModel->id);
        }
    }

    /**
     * 批量导出excel-导出到多个文件
     *
     * @param $params
     */
    public function handleExportExcel($params) {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        if (empty($params['site'])) {
            throw new \Exception("site is empty", 1);
        }
        // 设置当前租户
        tenancy()->initialize($params['site']);
        $exportLogInfo = AccessExportLog::find($params['log_id']);
        try {
            //读取数据
            $model = new AccessLog();
            $idList = $params['data'];
            $record = $model->whereIn('id', $idList)->get()->toArray();
            $writer = WriterEntityFactory::createXLSXWriter();
            $filename = public_path().$exportLogInfo['file'];
            if (!file_exists($filename)) {
                file_put_contents($filename, '');
            }
            $writer->openToFile($filename);
            // 数据数组（每个数组表示一个工作表的数据）
            //ip数组
            $sheetsDataList = [];
            $sheetsData = [];
            $sheetsData[] = ['编号', 'IP', '归属地', '访问地址', '请求时间', '流量'];
            foreach ($record as $key => $value) {
                $rows = [];
                $rows[] = $value['id'];
                $rows[] = $value['ip'];
                $rows[] = $value['ip_addr'];
                $rows[] = $value['route'];
                $rows[] = date('Y-m-d H:i:s', $value['log_time']);
                $rows[] = $this->formatBytes($value['content_size']);
                $sheetsData[] = $rows;
            }
            $sheetsDataList['IP统计'] = $sheetsData;
            $sheetsData = [];
            $sheetsData[] = ['编号', 'UA头', '归属地', '访问地址', '请求时间', '流量'];
            foreach ($record as $key => $value) {
                $rows = [];
                $rows[] = $value['id'];
                $rows[] = $value['ua_info'];
                $rows[] = $value['ip_addr'];
                $rows[] = $value['route'];
                $rows[] = date('Y-m-d H:i:s', $value['log_time']);
                $rows[] = $this->formatBytes($value['content_size']);
                $sheetsData[] = $rows;
            }
            $sheetsDataList['UA统计'] = $sheetsData;
            $sheetsData = [];
            $sheetsData[] = ['编号', '来源', '访问地址', '请求时间', '流量'];
            foreach ($record as $key => $value) {
                $rows = [];
                $rows[] = $value['id'];
                $rows[] = $value['referer'];
                $rows[] = $value['route'];
                $rows[] = date('Y-m-d H:i:s', $value['log_time']);
                $rows[] = $this->formatBytes($value['content_size']);
                $sheetsData[] = $rows;
            }
            $sheetsDataList['来源统计'] = $sheetsData;
            // 遍历工作表数据并创建工作表
            foreach ($sheetsDataList as $sheetName => $rows) {
                // 创建新工作表
                if ($sheetName != 'IP统计') {
                    $sheet = $writer->addNewSheetAndMakeItCurrent();
                } else {
                    $sheet = $writer->getCurrentSheet(); // 保持当前表
                }
                $sheet->setName($sheetName); // 设置工作表名称
                // 写入数据行
                foreach ($rows as $row) {
                    $rowEntity = WriterEntityFactory::createRowFromArray($row);
                    $writer->addRow($rowEntity);
                }
            }
            $writer->close();
        } catch (\Exception $th) {
            $details = $th->getMessage();
            throw $th;
        }
        //记录任务状态
        $logModel = AccessExportLog::where(['id' => $params['log_id']])->first();
        $logData = [
            'state' => AccessExportLog::EXPORT_COMPLETE,
        ];
        $logData['success_count'] = count($record);
        $logModel->update($logData);

        return true;
    }

//    /**
//     * 批量导出-合并文件
//     *
//     * @param $params
//     */
//    public function handleMergeFile($params = null) {
//        try {
//            set_time_limit(0);
//            ini_set('memory_limit', '2048M');
//            $dirPath = $params['dirPath'];
//            // 扫描目录下的所有文件
//            $existingFilePath = scandir($dirPath);
//            $existingFilePath = array_values(
//                array_filter($existingFilePath, function ($item) {
//                    return $item !== '.' && $item !== '..';
//                })
//            );
//            // $existingFilePath = ['0.xlsx', '1.xlsx', '2.xlsx'];
//            $dirPath = $params['data'];
//            $writer = WriterEntityFactory::createXLSXWriter();
//            $writer->openToFile($dirPath.'.xlsx');
//            $style = (new StyleBuilder())->setShouldWrapText(false)->build();
//            //写入标题
//            $title = ['编号', 'IP', '归属地', '访问地址', '请求时间', '流量'];
//            $row = WriterEntityFactory::createRowFromArray($title, $style);
//            $writer->addRow($row);
//            //循环读取文件，写入excel
//            foreach ($existingFilePath as $key => $path) {
//                // we need a reader to read the existing file...
//                $reader = ReaderEntityFactory::createXLSXReader();
//                $reader->setShouldPreserveEmptyRows(true);
//                $reader->open($dirPath.'/'.$path);
//                // let's read the entire spreadsheet...
//                foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
//                    // Add sheets in the new file, as we read new sheets in the existing one
//                    foreach ($sheet->getRowIterator() as $row) {
//                        // ... and copy each row into the new spreadsheet
//                        $row = WriterEntityFactory::createRowFromArray($row->toArray(), $style);
//                        $writer->addRow($row);
//                    }
//                }
//                $reader->close();
//            }
//            $writer->close();
//        } catch (\Throwable $th) {
//            // file_put_contents('C:\\Users\\Administrator\\Desktop\\aaaaa.txt', $th->getLine().$th->getMessage().$th->getTraceAsString(), FILE_APPEND);
//            $details = $th->getMessage();
//        }
//        //记录任务状态
//        $logModel = AccessExportLog::where(['id' => $params['log_id']])->first();
//        $logData = [
//            'state' => AccessExportLog::EXPORT_COMPLETE,
//        ];
//        if (isset($details)) {
//            $logData['details'] = $logModel->details.$details;
//        }
//        $logModel->update($logData);
//        //删除临时文件夹
//        if ($existingFilePath) {
//            foreach ($existingFilePath as $path) {
//                @unlink($dirPath.'/'.$path);
//            }
//            @rmdir($dirPath);
//        }
//    }


    /**
     *
     * @param Request $request
     *
     * @return array|mixed
     */
    private function getSearchCondition(Request $request) {
        $accessLogModel = new AccessLog();
        //显示IP ,  归属地 ,  请求数
        $searchStr = $request->input('search');
        $search = @json_decode($searchStr, true);
        $searchCondition = [];
        //选项页
        $tab = $request->input('tab', 'ip'); //ip , referer , ua_info
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
        $accessLogModel = $accessLogModel->where($searchCondition);
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
            $start_time = $srartTimeCoon->getTimestamp();
            $end_time = $endTimeCoon->getTimestamp();
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
                $srartTimeCoon = Carbon::now()->subMinutes(1);
            }
            $start_time = $srartTimeCoon->getTimestamp();
            $end_time = $endTimeCoon->getTimestamp();
        }
        $accessLogModel = $accessLogModel->whereBetween('created_at', [$start_time, $end_time]);

        return $accessLogModel;
    }

    /**
     *
     * @param Request $request
     *
     * @return array|mixed
     */
    private function getSelectCondition(Request $request) {
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
        $accessLogModel = $accessLogModel->whereBetween('created_at', [$start_time, $end_time]);

        return $accessLogModel;
    }

    public function getCntByCondition($conditionModel) {
        return $conditionModel->count();
    }

    /**
     * 导出进度
     *
     * @param $request 请求信息
     */
    public function exportProcess(Request $request) {
        $logId = $request->id;
        if (empty($logId)) {
            ReturnJson(true, trans('lang.param_empty'));
        }
        $logData = AccessExportLog::where('id', $logId)->first();
        if ($logData) {
            $logData = $logData->toArray();
        } else {
            ReturnJson(true, trans('lang.data_empty'));
        }
        $data = [
            'result' => true,
            'msg'    => '',
            'file'   => $logData['file'],
        ];
        $text = '';
        $updateTime = 0;
        $updatedTimestamp = strtotime($logData['updated_at']);
        if ($updatedTimestamp > $updateTime) {
            $updateTime = $updatedTimestamp;
        }
        switch ($logData['state']) {
            case AccessExportLog::EXPORT_INIT:
                $text = trans('lang.export_init_msg');
                break;
            case AccessExportLog::EXPORT_RUNNING:
                $text = trans('lang.export_running_msg').($logData['success_count'] + $logData['error_count']).'/'
                        .$logData['count'];
                break;
            case AccessExportLog::EXPORT_MERGING:
                $text = trans('lang.export_merging_msg');
                break;
            case AccessExportLog::EXPORT_COMPLETE:
                $text = trans('lang.export_complete_msg');
                break;
            default:
                # code...
                break;
        }
        $data['msg'] = $text;
        //五分钟没反应则提示

        if (time() > $updateTime + 86400 && $logData['state'] != AccessExportLog::EXPORT_COMPLETE) {
            $data = [
                'result' => false,
                'msg'    => trans('lang.time_out'),
            ];
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 新下载导出文件
     *
     * @param $request 请求信息
     */
    public function newExportFileDownload(Request $request) {
        $logId = $request->id;
        if (empty($logId)) {
            ReturnJson(true, trans('lang.param_empty'));
        }
        $logData = AccessExportLog::where('id', $logId)->first();
        if ($logData) {
            $logData = $logData->toArray();
        } else {
            ReturnJson(true, trans('lang.data_empty'));
        }
        if ($logData['state'] == AccessExportLog::EXPORT_COMPLETE) {
            $basePath = public_path();
            $file_path = $basePath.$logData['file'];
            $fileAbsultPath = str_replace(public_path(), '', $file_path);
            $domain = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'];
            $newDownUrl = $domain.$fileAbsultPath;
            ReturnJson(true, 'ok', ['down_url' => $newDownUrl]);
        }
        ReturnJson(true, trans('lang.file_not_exist'));
    }

    public function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

}
