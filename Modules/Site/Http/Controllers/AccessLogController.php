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
                         .' as targetField , count(*) as count, max(route) as route , max(log_time) as log_time , max(ip) as ip_refer  '
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
                                   ->selectRaw($field." as target_field ")
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
                            $accessLogModel = $accessLogModel->whereIn("ip_muti_second" , $value);
                        } elseif ($ip_muti_str == 'ip_muti_third') {
                            $searchCondition[] = ['ip_muti_third', 'in', $value];
                            $accessLogModel = $accessLogModel->whereIn("ip_muti_third" , $value);
                        } else {
                            $searchCondition[] = ['ip', 'in', $value];
                            $accessLogModel = $accessLogModel->whereIn("ip" , $value);
                        }
                    } elseif ($key == 'referer' && is_array($value)) {
                        $searchCondition['referer'] = $value;
                        $accessLogModel = $accessLogModel->whereIn("referer" , $value);
                    } elseif ($key == 'ua_info' && is_array($value)) {
                        $searchCondition['ua_info'] = $value;
                        $accessLogModel = $accessLogModel->whereIn("ua_info" , $value);
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
