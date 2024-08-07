<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\SystemValue;

// 系统设置
class SystemController extends CrudController {
    /**
     * syytem的Value值保存
     *
     * @param use Illuminate\Http\Request;
     *
     * @return Json bool
     */
    public function systemValueList(Request $request) {
        try {
            $ModelInstance = new SystemValue();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            if ($request->id) {
                $model->where('parent_id', $request->id);
            }
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
     * syytem的Value值保存
     *
     * @param use Illuminate\Http\Request;
     *
     * @return Json bool
     */
    public function systemValueStore(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = SystemValue::create($input);
            if (!$record) {
                ReturnJson(false, trans('lang.add_error'));
            }
            $this->syncSiteCache($record);
            ReturnJson(true, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * syytem的Value值更新
     *
     * @param use Illuminate\Http\Request;
     *
     * @return Json bool
     */
    public function systemValueUpdate(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = SystemValue::findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            $this->syncSiteCache($record);
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * syytem的Value值删除
     *
     * @param $ids 主键ID
     */
    public function systemValueDestroy(Request $request) {
        try {
            $this->ValidateInstance($request);
            $record = SystemValue::query();
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            $record->whereIn('id', $ids);
            if (!$record->delete()) {
                ReturnJson(false, trans('lang.delete_error'));
            }
            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * select value one data
     *
     * @param use Illuminate\Http\Request;
     */
    public function formValue(Request $request) {
        try {
            $record = SystemValue::findOrFail($request->id);
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * Child modification status
     *
     * @param use Illuminate\Http\Request;
     */
    public function valueChangeStatus(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = SystemValue::findOrFail($request->id);
            $record->status = $request->status;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 查询value-label格式列表
     *
     * @param       $request 请求信息
     * @param Array $where   查询条件数组 默认空数组
     */
    public function option(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $fileds = $request->HeaderLanguage == 'en' ? ['id as value', 'english_name as label']
                : ['id as value', 'name as label'];
            $record = $ModelInstance->GetListLabel($fileds, false, '', ['status' => 1]);
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * Query all children through parent
     *
     * @param use Illuminate\Http\Request;
     */
    public function valueList(Request $request) {
        try {
            $record = (new SystemValue)->where('hidden', 1)->where('parent_id', $request->parent_id)->get();
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * Child modification status
     *
     * @param use Illuminate\Http\Request;
     */
    public function valueChangeHidden(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = SystemValue::findOrFail($request->id);
            $record->hidden = $request->hidden;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            if($record->hidden == 1){
                $type = 'update';
            }else{
                $type = 'delete';
            }
            $this->syncSiteCache($record, $type);
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * @param         $record
     *
     */
    private function syncSiteCache($record, $type = 'update') {
        $keyList = ['white_ip_security_check', 'ip_white_rules', 'req_limit', 'window_time' , 'is_open_limit_req'];
        $key = $record['key'];
        $value = $record['value'];
        if (!empty($key) && in_array($key, $keyList)) {
            //写入缓存
            $siteKey = getSiteName();
            $domain = getSiteDomain();
            $url = $domain.'/api/third/sync-redis-val';
            $reqData = [
                'type' => $type,
                'key'  => $key,
                'val'  => $value,
            ];
            $reqData['sign'] = $this->makeSign($reqData, $siteKey);
            $response = Http::post($url, $reqData);
            $resp = $response->json();
            if (!empty($resp) && $resp['code'] == 200) {
                return true;
            } else {
                \Log::error('syncSiteCache---返回结果数据:'.json_encode($resp));

                return false;
            }
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

    /**
     * 修改状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeStatus(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $status = $request->status;
            $record->status = $status;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            //下面的子级全部变为隐藏状态
            $systemValueModel = new SystemValue();
            $childList = $systemValueModel->where('parent_id', $record->id)
                                            ->select(['id', 'key', 'value'])
                                            ->get()
                                            ->toArray();
            if($status == 1){
                $type = 'update';
            }else{
                $type = 'delete';
            }
            foreach ($childList as $childinfo) {
                $updData = ['hidden' => $status, 'status' => $status];
                $rs = $systemValueModel->where('id', $childinfo['id'])->update($updData);
                if($rs){
                    $this->syncSiteCache($childinfo , $type);
                }
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
