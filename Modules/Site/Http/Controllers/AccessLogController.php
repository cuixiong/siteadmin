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
    public function searchDroplist(Request $request) {
        try {
            $timeConst = [
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
            $time = $request->input('time');
            if (empty($time)) {
                //默认5分钟之前
                $time = '5m';
            }
            if ($time == '5m') {
                $srartTimeCoon = Carbon::now()->subMinutes(5);
                $list = $this->getCountInTable($srartTimeCoon, $now);
            } elseif ($time == '1h') {
                $srartTimeCoon = Carbon::now()->subHour();
                $list = $this->getCountInTable($srartTimeCoon, $now);
            } elseif ($time == 'today') {
                $srartTimeCoon = Carbon::today();
                $list = $this->getCountInTable($srartTimeCoon, $now);
            } elseif ($time == 'yesterday') {
                $srartTimeCoon = Carbon::yesterday();
                $list = $this->getCountAcrossTables($srartTimeCoon, $now);
            } elseif ($time == '7d') {
                $srartTimeCoon = Carbon::now()->subDays(7);
                $list = $this->getCountAcrossTables($srartTimeCoon, $now);
            } elseif ($time == '30d') {
                $srartTimeCoon = Carbon::now()->subDays(30);
                $list = $this->getCountAcrossTables($srartTimeCoon, $now);
            }
            $data['list'] = $list;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    // 查询单表内的记录数
    public function getCountInTable(Carbon $start, Carbon $end, $field = 'ip') {
        $date = $start->format('Ym');
        $accessLogModel = new AccessLog($date);
        $start_time = $start->getTimestamp();
        $end_time = $end->getTimestamp();
        $list = $accessLogModel::query()
                               ->whereBetween('created_at', [$start_time, $end_time])
                               ->groupBy($field)
                               ->orderBy('count', 'desc')
                               ->selectRaw($field.' , count(*) as count')
                               ->get()->toArray();
        $sum_cnt = 0;
        foreach ($list as $key => $value) {
            $sum_cnt += $value['count'];
        }
        foreach ($list as $key => &$value) {
            $value['percent'] = round($value['count'] / $sum_cnt * 100, 2);
            if ($field == 'ip') {
                $value['ip_addr'] = (new IPAddrService($value['ip']))->getAddrStrByIp();;
            }
        }

        return $list;
    }

    // 查询跨表的记录数
    public function getCountAcrossTables(Carbon $start, Carbon $end, $field = 'ip') {
        $startDate = $start->format('Ym');
        $endDate = $end->format('Ym');
        if ($startDate != $endDate) {
            //需要合并
            $accessLogModel = new AccessLog($startDate);
            $list_one = $accessLogModel::query()
                                       ->where('created_at', ">=", $start->getTimestamp())
                                       ->groupBy($field)
                                       ->orderBy('count', 'desc')
                                       ->selectRaw($field.' , count(*) as count')
                                       ->get()->toArray();
            $accessLogModel = new AccessLog($endDate);
            $list_two = $accessLogModel::query()
                                       ->where('created_at', "<=", $end->getTimestamp())
                                       ->groupBy($field)
                                       ->orderBy('count', 'desc')
                                       ->selectRaw($field.' , count(*) as count')
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
            foreach ($list_all as $key => &$value) {
                $value['percent'] = round($value['count'] / $sum_cnt * 100, 2);
                if ($field == 'ip') {
                    $value['ip_addr'] = '';
                }
            }

            return $list_all;
        } else {
            //直接调用
            return $this->getCountInTable($start, $end, $field);
        }
    }

    public function refererReportForms(Request $request) {
        //5分钟 , 今日, 昨日, 近一小时, 近七天, 近30天
        try {
            //显示IP ,  归属地 ,  请求数
            $now = Carbon::now();
            $time = $request->input('time');
            if (empty($time)) {
                //默认5分钟之前
                $time = '5m';
            }
            if ($time == '5m') {
                $srartTimeCoon = Carbon::now()->subMinutes(5);
                $list = $this->getCountInTable($srartTimeCoon, $now, 'referer');
            } elseif ($time == '1h') {
                $srartTimeCoon = Carbon::now()->subHour();
                $list = $this->getCountInTable($srartTimeCoon, $now, 'referer');
            } elseif ($time == 'today') {
                $srartTimeCoon = Carbon::today();
                $list = $this->getCountInTable($srartTimeCoon, $now, 'referer');
            } elseif ($time == 'yesterday') {
                $srartTimeCoon = Carbon::yesterday();
                $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'referer');
            } elseif ($time == '7d') {
                $srartTimeCoon = Carbon::now()->subDays(7);
                $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'referer');
            } elseif ($time == '30d') {
                $srartTimeCoon = Carbon::now()->subDays(30);
                $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'referer');
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
            $time = $request->input('time');
            if (empty($time)) {
                //默认5分钟之前
                $time = '5m';
            }
            if ($time == '5m') {
                $srartTimeCoon = Carbon::now()->subMinutes(5);
                $list = $this->getCountInTable($srartTimeCoon, $now, 'ua_info');
            } elseif ($time == '1h') {
                $srartTimeCoon = Carbon::now()->subHour();
                $list = $this->getCountInTable($srartTimeCoon, $now, 'ua_info');
            } elseif ($time == 'today') {
                $srartTimeCoon = Carbon::today();
                $list = $this->getCountInTable($srartTimeCoon, $now, 'ua_info');
            } elseif ($time == 'yesterday') {
                $srartTimeCoon = Carbon::yesterday();
                $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'ua_info');
            } elseif ($time == '7d') {
                $srartTimeCoon = Carbon::now()->subDays(7);
                $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'ua_info');
            } elseif ($time == '30d') {
                $srartTimeCoon = Carbon::now()->subDays(30);
                $list = $this->getCountAcrossTables($srartTimeCoon, $now, 'ua_info');
            }
            $data['list'] = $list;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
