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

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Site\Http\Models\AccessLog;
use Modules\Site\Services\IPAddrService;

class AccessLogController extends CrudController {
    public $sumCnt       = 0;
    public $accessTables = false;

    public function searchDroplist(Request $request) {
        try {
            $ip_muti_list_str = [
                'ip_muti_second' => '*.*.0.0/16(二段)',
                'ip_muti_third'  => '*.*.0.0/24(三段)',
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
                '1h'        => '近一小时',
                'today'     => '今日',
                'yesterday' => '昨日',
                '7d'        => '近七天',
                '30d'       => '近30天',
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

    public function ipReportForms(Request $request) {
        //5分钟 , 今日, 昨日, 近一小时, 近七天, 近30天
        try {
            //显示IP ,  归属地 ,  请求数
            $now = Carbon::now();
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $is_delete = $request->input('is_delete', 0);
            $searchCondition = [];
            if (!empty($search)) {
                foreach ($search as $key => $value) {
                    if ($key == 'ip' && !empty($value)) {
                        $ipList = explode(".", $value);
                        $ipStr = '';
                        foreach ($ipList as $forIp) {
                            if ($forIp != '*') {
                                $ipStr .= $forIp.".";
                            }
                        }
                        if(!empty($ipStr )){
                            $ipStr = rtrim($ipStr, ".");
                        }
                        $searchCondition[] = ['ip', 'like', "{$ipStr}%"];
                    }

                    if ($key == 'ip_addr' && !empty($value)) {
                        $searchCondition['ip_addr'] = $value;
                    }
                }
            }
            $time = $search['time'] ?? '';
            $selectTime = $search['selectTime'] ?? '';
            if (!empty($selectTime)) {
                $start_time = $selectTime[0] ?? '';
                $end_time = $selectTime[1] ?? '';
            }
            $list = [];
            if (!empty($start_time) && !empty($end_time)) {
                $srartTimeCoon = Carbon::parse($start_time);
                $endTimeCoon = Carbon::parse($end_time);
                $list = $this->getCountInTable($srartTimeCoon, $endTimeCoon, 'ip', $searchCondition, $is_delete);
            } else {
                if($time == '1m'){
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ip', $searchCondition, $is_delete);
                } elseif ($time == '5m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(5);
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ip', $searchCondition, $is_delete);
                } elseif ($time == '1h') {
                    $srartTimeCoon = Carbon::now()->subHour();
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ip', $searchCondition, $is_delete);
                } elseif ($time == 'today') {
                    $srartTimeCoon = Carbon::today();
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ip', $searchCondition, $is_delete);
                } elseif ($time == 'yesterday') {
                    $srartTimeCoon = Carbon::yesterday();
                    $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'ip', $searchCondition, $is_delete);
                } elseif ($time == '7d') {
                    $srartTimeCoon = Carbon::now()->subDays(7);
                    $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'ip', $searchCondition, $is_delete);
                } elseif ($time == '30d') {
                    $srartTimeCoon = Carbon::now()->subDays(30);
                    $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'ip', $searchCondition, $is_delete);
                } else {
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ip', $searchCondition, $is_delete);
                }
            }
            //多段IP
            $ip_muti_str = $search['ip_muti_str'] ?? 'ip_muti_second';
            if (!empty($list) && is_array($list) && $ip_muti_str != 'ip_muti_full') {
                if ($ip_muti_str == 'ip_muti_second') {
                    $ban_ip_level = 2;
                } elseif ($ip_muti_str == 'ip_muti_third') {
                    $ban_ip_level = 1;
                } else {
                    $ban_ip_level = 0;
                }
                $afterIpListCnt = [];
                foreach ($list as $key => $value) {
                    $converIp = $this->converIp($ban_ip_level, $value['ip']);
                    if (empty($afterIpListCnt[$converIp])) {
                        $afterIpListCnt[$converIp]['route'] = $value['route'];
                        $afterIpListCnt[$converIp]['log_time'] = $value['log_time'];
                        $afterIpListCnt[$converIp]['ip_addr'] = $value['ip_addr'];
                        $afterIpListCnt[$converIp]['count'] = $value['count'];
                    } else {
                        $afterIpListCnt[$converIp]['count'] += $value['count'];
                    }
                }
                $afterIpList = [];
                foreach ($afterIpListCnt as $key => $value) {
                    $addData = [];
                    $addData['ip'] = $key;
                    $addData['count'] = $value['count'];
                    $addData['percent'] = round($value['count'] / $this->sumCnt * 100, 2);
                    $addData['ip_addr'] = $value['ip_addr'];
                    $addData['route'] = $value['route'];
                    $addData['log_time'] = $value['log_time'];
                    $afterIpList[] = $addData;
                }
                $list = $afterIpList;
            }

            // 查询偏移量
            if (!empty($list) && $this->accessTables) {
                $request = request();
                $list = array_values($list);
                $data['total'] = count($list);
                //二维数组根据字段排序
                $counts = array_column($list, 'count');
                array_multisort($counts, SORT_DESC, $list);
                if (!empty($request->pageNum) && !empty($request->pageSize)) {
                    $offset = ($request->pageNum - 1) * $request->pageSize;
                    $list = array_slice($list, $offset, $request->pageSize);
                }
            }
            $data['list'] = $list;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function refererReportForms(Request $request) {
        //5分钟 , 今日, 昨日, 近一小时, 近七天, 近30天
        try {
            //显示IP ,  归属地 ,  请求数
            $now = Carbon::now();
            $list = [];
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            if (!empty($search)) {
                foreach ($search as $key => $value) {
                    if ($key == 'referer' && !empty($value)) {
                        $searchCondition['referer'] = $value;
                    }
                }
            }
            $is_delete = $request->input('is_delete', 0);
            $time = $search['time'] ?? '';
            $selectTime = $search['selectTime'] ?? '';
            if (!empty($selectTime)) {
                $start_time = $selectTime[0] ?? '';
                $end_time = $selectTime[1] ?? '';
            }
            if (!empty($start_time) && !empty($end_time)) {
                $srartTimeCoon = Carbon::parse($start_time);
                $endTimeCoon = Carbon::parse($end_time);
                $list = $this->getCountInTable($srartTimeCoon, $endTimeCoon, 'referer', $searchCondition, $is_delete);
            } else {
                if ($time == '1m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'referer', $searchCondition, $is_delete);
                }else if ($time == '5m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(5);
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'referer', $searchCondition, $is_delete);
                } elseif ($time == '1h') {
                    $srartTimeCoon = Carbon::now()->subHour();
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'referer', $searchCondition, $is_delete);
                } elseif ($time == 'today') {
                    $srartTimeCoon = Carbon::today();
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'referer', $searchCondition, $is_delete);
                } elseif ($time == 'yesterday') {
                    $srartTimeCoon = Carbon::yesterday();
                    $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'referer', $searchCondition, $is_delete);
                } elseif ($time == '7d') {
                    $srartTimeCoon = Carbon::now()->subDays(7);
                    $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'referer', $searchCondition, $is_delete);
                } elseif ($time == '30d') {
                    $srartTimeCoon = Carbon::now()->subDays(30);
                    $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'referer', $searchCondition, $is_delete);
                } else {
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'referer', $searchCondition, $is_delete);
                }
            }
            // 查询偏移量
            if (!empty($list) && $this->accessTables) {
                $request = request();
                $list = array_values($list);
                $data['total'] = count($list);
                //二维数组根据字段排序
                $counts = array_column($list, 'count');
                array_multisort($counts, SORT_DESC, $list);
                if (!empty($request->pageNum) && !empty($request->pageSize)) {
                    $offset = ($request->pageNum - 1) * $request->pageSize;
                    $list = array_slice($list, $offset, $request->pageSize);
                }
            }
            $data['list'] = $list;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function uaReportForms(Request $request) {
        //5分钟 , 今日, 昨日, 近一小时, 近七天, 近30天
        try {
            //显示IP ,  归属地 ,  请求数
            $now = Carbon::now();
            $list = [];
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            if (!empty($search)) {
                foreach ($search as $key => $value) {
                    if ($key == 'ua_info' && !empty($value)) {
                        $searchCondition['ua_info'] = $value;
                    }
                }
            }
            $time = $search['time'] ?? '';
            $selectTime = $search['selectTime'] ?? '';
            if (!empty($selectTime)) {
                $start_time = $selectTime[0] ?? '';
                $end_time = $selectTime[1] ?? '';
            }
            $is_delete = $request->input('is_delete', 0);
            if (!empty($start_time) && !empty($end_time)) {
                $srartTimeCoon = Carbon::parse($start_time);
                $endTimeCoon = Carbon::parse($end_time);
                $list = $this->getCountInTable($srartTimeCoon, $endTimeCoon, 'ua_info', $searchCondition, $is_delete);
            } else {
                if ($time == '1m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ua_info', $searchCondition, $is_delete);
                } elseif ($time == '5m') {
                    $srartTimeCoon = Carbon::now()->subMinutes(5);
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ua_info', $searchCondition, $is_delete);
                } elseif ($time == '1h') {
                    $srartTimeCoon = Carbon::now()->subHour();
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ua_info', $searchCondition, $is_delete);
                } elseif ($time == 'today') {
                    $srartTimeCoon = Carbon::today();
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ua_info', $searchCondition, $is_delete);
                } elseif ($time == 'yesterday') {
                    $srartTimeCoon = Carbon::yesterday();
                    $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'ua_info', $searchCondition, $is_delete);
                } elseif ($time == '7d') {
                    $srartTimeCoon = Carbon::now()->subDays(7);
                    $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'ua_info', $searchCondition, $is_delete);
                } elseif ($time == '30d') {
                    $srartTimeCoon = Carbon::now()->subDays(30);
                    $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'ua_info', $searchCondition, $is_delete);
                }else{
                    $srartTimeCoon = Carbon::now()->subMinutes(1);
                    $list = $this->getCountInTable($srartTimeCoon, $now, 'ua_info', $searchCondition, $is_delete);
                }
            }
            // 查询偏移量
            if (!empty($list) && $this->accessTables) {
                $request = request();
                $list = array_values($list);
                $data['total'] = count($list);
                //二维数组根据字段排序
                $counts = array_column($list, 'count');
                array_multisort($counts, SORT_DESC, $list);
                if (!empty($request->pageNum) && !empty($request->pageSize)) {
                    $offset = ($request->pageNum - 1) * $request->pageSize;
                    $list = array_slice($list, $offset, $request->pageSize);
                }
            }
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
        $date = $start->format('Ym');
        $accessLogModel = new AccessLog($date);
        $start_time = $start->getTimestamp();
        $end_time = $end->getTimestamp();
        $list = $accessLogModel::query()
                               ->where($condition)
                               ->whereBetween('created_at', [$start_time, $end_time])
                               ->groupBy($field)
                               ->orderBy('count', 'desc')
                               ->selectRaw(
                                   $field.' , count(*) as count, max(route) as route , max(log_time) as log_time  '
                               );
        // 查询偏移量
        $request = request();
        if (!empty($request->pageNum) && !empty($request->pageSize)) {
            $list = $list->offset(($request->pageNum - 1) * $request->pageSize)->limit($request->pageSize);
        }
        $list = $list->get()->toArray();
        $sum_cnt = 0;
        foreach ($list as $key => $value) {
            $sum_cnt += $value['count'];
        }
        $this->sumCnt = $sum_cnt;
        foreach ($list as $key => &$value) {
            $value['percent'] = round($value['count'] / $sum_cnt * 100, 2);
            if ($field == 'ip') {
                $value['ip_addr'] = (new IPAddrService($value['ip']))->getAddrStrByIp();
            }
            $value['log_time'] = date("Y-m-d H:i:s", $value['log_time']);
        }
        if (!empty($list)) {
            $list = array_values($list);
        }

        return $list;
    }

    // 查询跨表的记录数
    public function getCountAcrossTables(Carbon $start, Carbon $end, $field = 'ip', $condition = [], $is_delete = false
    ) {
        if ($is_delete) {
            return $this->delAccessDataList($start, $end, $condition);
        }
        $startDate = $start->format('Ym');
        $endDate = $end->format('Ym');
        if ($startDate != $endDate) {
            $this->accessTables = true;
            $start_time = $start->getTimestamp();
            $end_time = $end->getTimestamp();
            //需要合并
            $accessLogModel = new AccessLog($startDate);
            $list_one = $accessLogModel::query()
                                       ->where($condition)
                                       ->whereBetween('created_at', [$start_time, $end_time])
                                       ->groupBy($field)
                                       ->orderBy('count', 'desc')
                                       ->selectRaw(
                                           $field
                                           .' , count(*) as count, max(route) as route , max(log_time) as log_time '
                                       )
                                       ->get()->toArray();
            $accessLogModel = new AccessLog($endDate);
            $list_two = $accessLogModel::query()
                                       ->where($condition)
                                       ->whereBetween('created_at', [$start_time, $end_time])
                                       ->groupBy($field)
                                       ->orderBy('count', 'desc')
                                       ->selectRaw(
                                           $field
                                           .' , count(*) as count, max(route) as route , max(log_time) as log_time '
                                       )
                                       ->get()->toArray();
            $sum_cnt = 0;
            $list_all = [];
            foreach ($list_one as $key => $value) {
                $sum_cnt += $value['count'];
                $list_all[$value[$field]] = $value;
            }
            foreach ($list_two as $key => $value) {
                $sum_cnt += $value['count'];
                if (isset($list_all[$value[$field]])) {
                    $list_all[$value[$field]]['count'] += $value['count'];
                } else {
                    $list_all[$value[$field]] = $value;
                }
            }
            $this->sumCnt = $sum_cnt;
            foreach ($list_all as $key => &$value) {
                $value['percent'] = round($value['count'] / $sum_cnt * 100, 2);
                if ($field == 'ip') {
                    $value['ip_addr'] = (new IPAddrService($value['ip']))->getAddrStrByIp();
                }
                $value['log_time'] = date("Y-m-d H:i:s", $value['log_time']);
            }

            return $list_all;
        } else {
            //直接调用
            return $this->getCountInTable($start, $end, $field, $condition);
        }
    }

    // 查询单表内的记录数
    public function delAccessData(Carbon $start, Carbon $end, $condition = []) {
        $date = $start->format('Ym');
        $accessLogModel = new AccessLog($date);
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

    public function delAccessDataList(Carbon $start, Carbon $end, $condition = []) {
        $startDate = $start->format('Ym');
        $endDate = $end->format('Ym');
        if ($startDate != $endDate) {
            //需要合并
            $start_time = $start->getTimestamp();
            $end_time = $end->getTimestamp();
            $accessLogModel = new AccessLog($startDate);
            $list_one_cnt = $accessLogModel::query()
                                           ->where($condition)
                                           ->whereBetween('created_at', [$start_time, $end_time])
                                           ->count();
            if ($list_one_cnt > 0) {
                $accessLogModel::query()
                               ->where($condition)
                               ->whereBetween('created_at', [$start_time, $end_time])
                               ->delete();
            }
            $accessLogModel = new AccessLog($endDate);
            $list_two_cnt = $accessLogModel::query()
                                           ->where($condition)
                                           ->whereBetween('created_at', [$start_time, $end_time])
                                           ->count();
            if ($list_two_cnt > 0) {
                $accessLogModel::query()
                               ->where($condition)
                               ->where('created_at', "<=", $end->getTimestamp())
                               ->delete();
            }

            return $list_one_cnt + $list_two_cnt;
        } else {
            //直接调用
            return $this->delAccessData($start, $end, $condition);
        }
    }

    public function accessDetailList(Request $request) {
        try {
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            if (!empty($search)) {
                foreach ($search as $key => $value) {
                    if ($key == 'ip' && !empty($value)) {
                        $ipList = explode(".", $value);
                        $ipStr = '';
                        foreach ($ipList as $forIp) {
                            if ($forIp != '*') {
                                $ipStr .= $forIp.".";
                            }
                        }
                        if(!empty($ipStr )){
                            $ipStr = rtrim($ipStr, ".");
                        }
                        $searchCondition[] = ['ip', 'like', "{$ipStr}%"];
                    }
                    if ($key == 'ip_addr' && !empty($value)) {
                        $searchCondition['ip_addr'] = $value;
                    }
                    if ($key == 'ua_info' && !empty($value)) {
                        $searchCondition['ua_info'] = $value;
                    }
                    if ($key == 'referer' && isset($value)) {
                        $searchCondition['referer'] = $value;
                    }
                }
            }


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
                }else if ($time == '5m') {
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
            $data['list'] = $list;
            $data['count'] = count($list);
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function getAcrossTablesDetail(Carbon $start, Carbon $end, $condition = []) {
        $startDate = $start->format('Ym');
        $endDate = $end->format('Ym');
        if ($startDate != $endDate) {
            //需要合并
            $accessLogModel = new AccessLog($startDate);
            $list_one = $accessLogModel::query()
                                       ->where($condition)
                                       ->where('created_at', ">=", $start->getTimestamp())
                                       ->where('created_at', "<=", $end->getTimestamp())
                                       ->orderBy('log_time', 'desc')
                                       ->get()->toArray();
            $accessLogModel = new AccessLog($endDate);
            $list_two = $accessLogModel::query()
                                       ->where($condition)
                                       ->where('created_at', ">=", $start->getTimestamp())
                                       ->where('created_at', "<=", $end->getTimestamp())
                                       ->orderBy('log_time', 'desc')
                                       ->get()->toArray();
            $list_all = array_merge($list_one, $list_two);
            if (!empty($list_all)) {
                $list_all = array_values($list_all);
            }

            return $list_all;
        } else {
            //直接调用
            $accessLogModel = new AccessLog($startDate);
            $list_all = $accessLogModel::query()
                                       ->where($condition)
                                       ->where('created_at', ">=", $start->getTimestamp())
                                       ->where('created_at', "<", $end->getTimestamp())
                                       ->orderBy('log_time', 'desc')
                                       ->get()->toArray();

            return $list_all;
        }
    }

    public function accessLogDel(Request $request) {
        try {
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            $searchCondition = [];
            $ip = $request->input('ip', '');
            if (!empty($ip)) {
                $ipList = explode(".", $ip);
                $ipStr = '';
                foreach ($ipList as $forIp) {
                    if ($forIp != '*') {
                        $ipStr .= $forIp.".";
                    }
                }
                $searchCondition[] = ['ip', 'like', "{$ipStr}%"];
            }
            $ua_info = $request->input('ua_info', '');
            if (!empty($ua_info)) {
                $searchCondition['ua_info'] = $ua_info;
            }
            $referer = $request->input('referer', '');
            if (!empty($referer)) {
                $searchCondition['referer'] = $referer;
            }
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
            $delCnt = $this->accessLogDelAll($srartTimeCoon, $endTimeCoon, $searchCondition);
            $data['count'] = $delCnt;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function accessLogDelAll(Carbon $start, Carbon $end, $condition = []) {
        $startDate = $start->format('Ym');
        $endDate = $end->format('Ym');
        if ($startDate != $endDate) {
            //需要合并
            $accessLogModel = new AccessLog($startDate);
            $listOneCnt = $accessLogModel::query()
                                         ->where($condition)
                                         ->where('created_at', ">=", $start->getTimestamp())
                                         ->where('created_at', "<", $end->getTimestamp())
                                         ->count();
            if ($listOneCnt > 0) {
                $accessLogModel::query()
                               ->where($condition)
                               ->where('created_at', ">=", $start->getTimestamp())
                               ->where('created_at', "<", $end->getTimestamp())
                               ->delete();
            }
            $accessLogModel = new AccessLog($endDate);
            $listTwoCnt = $accessLogModel::query()
                                         ->where($condition)
                                         ->where('created_at', ">=", $start->getTimestamp())
                                         ->where('created_at', "<", $end->getTimestamp())
                                         ->count();
            if ($listTwoCnt > 0) {
                $accessLogModel::query()
                               ->where($condition)
                               ->where('created_at', ">=", $start->getTimestamp())
                               ->where('created_at', "<", $end->getTimestamp())
                               ->delete();
            }

            return $listOneCnt + $listTwoCnt;
        } else {
            //直接调用
            $accessLogModel = new AccessLog($startDate);
            $delCount = $accessLogModel::query()
                                       ->where($condition)
                                       ->where('created_at', ">=", $start->getTimestamp())
                                       ->where('created_at', "<", $end->getTimestamp())
                                       ->count();
            if ($delCount > 0) {
                $accessLogModel::query()
                               ->where($condition)
                               ->where('created_at', ">=", $start->getTimestamp())
                               ->where('created_at', "<", $end->getTimestamp())
                               ->delete();
            }

            return $delCount;
        }
    }
}
