<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\City;

class CityController extends CrudController {

    /**
     * 查询列表页
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
//            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
//            if (!empty($request->order)) {
//                $model = $model->orderBy($request->order, $sort);
//            } else {
//                $model = $model->orderBy('sort', $sort)->orderBy('id', 'ASC');
//            }
            $model = $model->orderBy('sort', 'ASC')->orderBy('id', 'ASC');
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
            //国家列表
            $data['country_id'] = City::getCountryList();
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            $data['type'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'City_Type', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
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
            $input = $request->input();
            $type = $input['type'] ?? 1;
            $record = City::query()->where("status", 1)
                          ->where("type", $type)
                          ->orderBy('sort', 'ASC')
                          ->selectRaw('id as value,name as label')
                          ->get()->toArray();
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
