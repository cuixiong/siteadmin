<?php

namespace Modules\Site\Http\Controllers;

use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\City;
use Modules\Admin\Http\Models\Country;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\MessageCategory;
use Modules\Site\Http\Models\MessageLanguageVersion;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\Region;

class ContactUsController extends CrudController {
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
}
