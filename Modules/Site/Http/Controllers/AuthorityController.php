<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\QuoteCategory;

class AuthorityController extends CrudController
{
    public function options(Request $request){
        $options = [];
        $codes = ['Switch_State','quote_cage'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
            }
        }
        $options['product_category'] = (new ProductsCategory())->GetListLabel(['id as value','name as label','pid','id'],true,'pid',['status' => 1]);
        $options['quote_category'] = (new QuoteCategory())->GetListLabel(['id as value','name as label'],false,'',['status' => 1]);
        ReturnJson(TRUE,'', $options);
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
