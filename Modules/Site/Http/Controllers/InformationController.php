<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\NewsCategory;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\Region;
use Modules\Site\Http\Models\UrlFilterEdition;

class InformationController extends CrudController {
    /**
     * 获取搜索下拉列表
     *
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request) {
        try {
            // 新闻类型
            $data['pay_type'] = (new NewsCategory())->GetListLabel(['id as value', 'name as label'], false, '',
                                                                   ['status' => 1]);

            // 行业分类
            $data['category'] = (new ProductsCategory())->GetList(['id as value', 'name as label', 'id', 'pid'], true,
                                                                  'pid', ['status' => 1]);
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            // 是否显示首页
            $data['show_home'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Show_Home_State', 'status' => 1], ['sort' => 'ASC']
            );
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    public function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            // 虚拟点击量
            if (!isset($input['hits']) || empty($input['hits'])) {
                $input['hits'] = mt_rand(200, 500);
            }
            // 出版时间为空则设定为当前时间
            if (!isset($input['upload_at']) || empty($input['upload_at'])) {
                $input['upload_at'] = time();
            }
            // 过滤url参数  过滤掉特殊符号%，&之类的
            $urlFdModel = new UrlFilterEdition();
            $urlFilterList = $urlFdModel->where("status", 1)->orderBy('sort' , 'desc')->pluck("name")->toArray();
            $url = $request->input('url');
            // 转义规则字符，并使用 | 连接起来，形成正则表达式模式
            $pattern = '/' . implode('|', array_map('preg_quote', $urlFilterList, array_fill(0, count($urlFilterList), '/'))) . '/u';
            $filteredString = preg_replace($pattern, '', $url);
            $input['url'] = $filteredString;


            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                ReturnJson(false, trans('lang.add_error'));
            }
            ReturnJson(true, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单个更新
     *
     * @param $request 请求信息
     */
    protected function update(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            // 虚拟点击量
            if ((!isset($input['hits']) || empty($input['hits'])) && empty($record->hits)) {
                $input['hits'] = mt_rand(200, 500);
            }
            // 出版时间为空则设定为当前时间
            if ((!isset($input['upload_at']) || empty($input['upload_at'])) && empty($record->upload_at)) {
                $input['upload_at'] = time();
            }
            // 过滤url参数  过滤掉特殊符号%，&之类的
            $urlFdModel = new UrlFilterEdition();
            $urlFilterList = $urlFdModel->where("status", 1)->orderBy('sort' , 'desc')->pluck("name")->toArray();
            $url = $request->input('url');
            // 转义规则字符，并使用 | 连接起来，形成正则表达式模式
            $pattern = '/' . implode('|', array_map('preg_quote', $urlFilterList, array_fill(0, count($urlFilterList), '/'))) . '/u';
            $filteredString = preg_replace($pattern, '', $url);
            $input['url'] = $filteredString;


            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 修改首页状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeHome(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->show_home = $request->status;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
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
