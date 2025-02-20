<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\BanWhiteList as AdminBanWhiteList;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Publisher as adminPublisher;
use Modules\Site\Http\Models\Publisher as Publisher;

class PublisherController extends CrudController {


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
            $model = $model->orderBy('id', 'desc');
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
     * 获取搜索下拉列表
     *
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request) {
        try {
            $data = [];
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function sync() {
        $admin_pulisher_list = adminPublisher::all()->map(function ($item) {
            return $item->getAttributes();
        })->toArray();
        foreach ($admin_pulisher_list as $admin_info) {
            $pulishId = $admin_info['id'];
            $PublisherInfo = Publisher::query()->where("id" , $pulishId)
                ->orWhere("name" , $admin_info['name'])->first();
            if (empty($PublisherInfo)) {
                Publisher::query()->insert($admin_info);
            }
        }
        ReturnJson(true, trans('lang.request_success'), []);
    }

}
