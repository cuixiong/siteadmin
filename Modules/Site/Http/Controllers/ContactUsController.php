<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\City;
use Modules\Admin\Http\Models\Country;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\ContactUs;
use Modules\Site\Http\Models\MessageCategory;
use Modules\Site\Http\Models\MessageLanguageVersion;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\Region;

class ContactUsController extends CrudController {
    public $signKey = '62d9048a8a2ee148cf142a0e6696ab26';

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
            foreach ($record as &$value) {
                if (!empty($value['send_email_time'])) {
                    $value['send_email_time_str'] = date('Y-m-d H:i:s', $value['send_email_time']);
                } else {
                    $value['send_email_time_str'] = '';
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

    public function options(Request $request) {
        $options = [];
        $codes = ['Switch_State', 'Channel_Type', 'Buy_Time'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select('code', 'value', $NameField)
                               ->orderBy('sort', 'asc')->get()->toArray();
        if (!empty($data)) {
            foreach ($data as $map) {
                $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
            }
        }
        $options['categorys'] = (new MessageCategory)->GetListLabel(['id as value', 'name as label'], false, '',
                                                                    ['status' => 1]);
        $options['language_version'] = (new MessageLanguageVersion())->GetListLabel(['id as value', 'name as label'],
                                                                                    false, '', ['status' => 1]);
        $options['country'] = Country::where('status', 1)->select('id as value', 'name as label')->orderBy(
            'sort', 'asc'
        )->get()->toArray();
        $provinces = City::where(['status' => 1, 'type' => 1])->select('id as value', 'name as label')->orderBy(
            'id', 'asc'
        )->get()->toArray();
        foreach ($provinces as $key => $province) {
            $cities = City::where(['status' => 1, 'type' => 2, 'pid' => $province['value']])->select(
                'id as value', 'name as label'
            )->orderBy('id', 'asc')->get()->toArray();
            $provinces[$key]['children'] = $cities;
        }
        $options['city'] = $provinces;
        ReturnJson(true, '', $options);
    }

    /**
     * 修改状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeStatus(Request $request) {
        try {
            $ids = $request->ids;
            if (empty($ids)) {
                if (empty($request->id)) {
                    ReturnJson(false, 'id is empty');
                }
                $ids = [$request->id];
            } else {
                if (!is_array($ids)) {
                    $ids = explode(",", $ids);
                }
            }
            foreach ($ids as $id) {
                $record = $this->ModelInstance()->findOrFail($id);
                $record->status = $request->status;
                if (!$record->save()) {
                    ReturnJson(false, trans('lang.update_error'));
                }
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 批量修改下拉参数
     *
     * @param $request 请求信息
     */
    public function batchUpdateParam(Request $request) {
        $field = [
            [
                'name'  => '状态',
                'value' => 'status',
                'type'  => '2',
            ],
        ];
        array_unshift($field, ['name' => '请选择', 'value' => '', 'type' => '']);
        ReturnJson(true, trans('lang.request_success'), $field);
    }

    /**
     * 批量修改下拉参数子项
     *
     * @param $request 请求信息
     */
    public function batchUpdateOption(Request $request) {
        $input = $request->all();
        $keyword = $input['keyword'];
        $data = [];
        if ($keyword == 'status') {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Show_Home_State', 'status' => 1], ['sort' => 'ASC']
            );
        } elseif ($keyword == 'country_id') {
            $data = (new Region())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1],
                                                 ['sort' => 'ASC']);
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 批量修改
     *
     * @param $request 请求信息
     */
    public function batchUpdate(Request $request) {
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $keyword = $input['keyword'] ?? '';
        $value = $input['value'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作
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
        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            // $data['result_count'] = $model->update([$keyword => $value]);
            // 批量操作无法触发添加日志的功能，但我领导要求有日志
            $newIds = $model->pluck('id');
            foreach ($newIds as $id) {
                $record = $this->ModelInstance()->find($id);
                if ($record) {
                    $record->$keyword = $value;
                    $record->save();
                }
            }
            ReturnJson(true, trans('lang.update_success'));
        }
    }

    /**
     *  重新发送邮件
     */
    public function againSendEmail(Request $request) {
        try {
            $ids = [];
            if (!empty($request->ids)) {
                if (is_array($request->ids)) {
                    $ids = $request->ids;
                } else {
                    $ids = [$request->ids];
                }
            } elseif (!empty($request->id)) {
                $ids = [$request->id];
            }
            $site = $request->header('site');
            $domain = Site::where('name', $site)->value("domain");
            if (empty($domain)) {
                ReturnJson(false, '站点配置异常');
            }
            if (strpos($domain, '://') === false) {
                $domain = 'https://'.$domain;
            }
            $url = $domain.'/api/third/send-email';
            $sucCnt = 0;
            $errMsg = [];
            $mcate_list = MessageCategory::query()->pluck('code', 'id')->toArray();
            foreach ($ids as $id) {
                $record = (new ContactUs())->findOrFail($id);
                if(empty($record )){
                    continue;
                }
                //已支付与已完成
                $code = $mcate_list[$record->category_id];
                $reqData = [
                    'id'   => $id,
                    'code' => $code,
                ];
                $reqData['sign'] = $this->makeSign($reqData, $this->signKey);
                //\Log::error('返回结果数据:'.json_encode([$url, $reqData]).'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
                $response = Http::post($url, $reqData);
                $resp = $response->json();
                if (!empty($resp) && $resp['code'] == 200) {
                    $sucCnt++;
                } else {
                    $errMsg[] = $resp;
                }
            }
            if (empty($errMsg)) {
                ReturnJson(true, "发送成功:{$sucCnt}次");
            } else {
                \Log::error('返回结果数据:'.json_encode($errMsg));
                ReturnJson(false, '发送失败,未知错误');
            }
        } catch (\Exception $e) {
            ReturnJson(true, '未知错误');
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
