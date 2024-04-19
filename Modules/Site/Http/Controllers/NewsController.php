<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\NewsCategory;
use Modules\Site\Http\Models\ProductsCategory;

class NewsController extends CrudController {
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
    protected function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            // 虚拟点击量
            if (!isset($input['hits']) || empty($input['hits'])) {
                $input['hits'] = mt_rand(100, 1000);
            }
            // 出版时间为空则设定为当前时间
            if (!isset($input['upload_at']) || empty($input['upload_at'])) {
                $input['upload_at'] = time();
            }
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
                $input['hits'] = mt_rand(100, 1000);
            }
            // 出版时间为空则设定为当前时间
            if ((!isset($input['upload_at']) || empty($input['upload_at'])) && empty($record->upload_at)) {
                $input['upload_at'] = time();
            }
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
}
