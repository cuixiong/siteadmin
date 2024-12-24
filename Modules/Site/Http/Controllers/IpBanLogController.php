<?php
/**
 * IpBanLogController.php UTF-8
 * ip封禁日志
 *
 * @date    : 2024/7/18 14:57 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Site\Http\Models\BanWhiteList;
use Modules\Site\Http\Models\CouponUser;
use Modules\Site\Http\Models\IpBanLog;

class IpBanLogController extends CrudController {
    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    public function list(Request $request) {
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
                $model = $model->orderBy('id', 'desc');
            }
            $record = $model->get();
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
     * IP解封
     *
     * @param Request $request
     *
     */
    public function IpUnban(Request $request) {
        try {
            $id = $request->input('id', 0);
            if (empty($id)) {
                ReturnJson(false, trans('lang.param_error'));
            }
            $ipBanLog = IpBanLog::query()->findOrFail($id);
            $ip = $ipBanLog->ip;
            $muti_ip = $ipBanLog->muti_ip;
            if (!empty($muti_ip)) {
                $key = $muti_ip;
            } else {
                $key = $ip;
            }
            //写入缓存
            $domain = getSiteDomain();
            $url = $domain.'/api/third/clear-ban';
            $reqData = [
                'type' => 1,
                'key'  => $key,
            ];
            $signKey = '62d9048a8a2ee148cf142a0e6696ab26';
            $reqData['sign'] = $this->makeSign($reqData, $signKey);
            $response = Http::post($url, $reqData);
            $resp = $response->json();
            if (!empty($resp) && $resp['code'] == 200) {
                ReturnJson(true, trans('lang.request_success'), []);
            } else {
                ReturnJson(false, '清除失败');
            }
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     *  添加白名单
     */
    public function addWhiteList(Request $request) {
        try {
            $id = $request->input('id', 0);
            if (empty($id)) {
                ReturnJson(false, trans('lang.param_error'));
            }
            $ipBanLog = IpBanLog::query()->findOrFail($id);
            $ip = $ipBanLog->ip;
            $ipList = explode('.', $ip);
            //最后一段变为*
            $ipList[count($ipList) - 1] = '*';
            $key = implode('.', $ipList);
            $remark = $ipBanLog->ip_addr;
            $banWhiteId = BanWhiteList::query()->where('remark', $remark)->value('id');
            if (empty($banWhiteId)) {
                $whiteIpList[] = $key;
                $addWhiteData = [
                    'type'    => 1,
                    'ban_str' => json_encode($whiteIpList),
                    'remark'  => $remark,
                ];
                $res = BanWhiteList::create($addWhiteData);
                ReturnJson(true, trans('lang.request_success'), []);
            } else {
                $banwhiteInfo = BanWhiteList::find($banWhiteId);
                $whiteIpList = @json_decode($banwhiteInfo->ban_str, true);
                if(!in_array($key, $whiteIpList)){
                    $whiteIpList[] = $key;
                    $banwhiteInfo->ban_str = json_encode($whiteIpList);
                    $res = $banwhiteInfo->save();
                    ReturnJson(true, trans('lang.request_success'), []);
                }else{
                    ReturnJson(false, 'ip已添加白名单');
                }
            }
            ReturnJson(false, '添加失败');
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function copyField(Request $request) {
        try {
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            $ids = $request->input('ids', '');
            if(!empty($ids )){
                $idList = explode(',' , $ids);
                $model = $model->whereIn("id" , $idList);
            }
            $field = $request->input('field', 'ip');
            $record = $model->groupBy($field)->pluck($field)->toArray();
            $handlerFieldList = [];

            foreach ($record as $key => $value){
                if($field == 'ip'){
                    $handlerFieldStr = 'deny '.$value;
                }else{
                    $handlerFieldStr = '';
                }
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

    public function makeSign($data, $signkey) {
        unset($data['sign']);
        $signStr = '';
        ksort($data);
        foreach ($data as $key => $value) {
            $signStr .= $key.'='.$value.'&';
        }
        $signStr .= "key={$signkey}";

        //dump($signStr);
        return md5($signStr);
    }
}
