<?php

namespace Modules\Site\Http\Controllers;

use Foolz\SphinxQL\SphinxQL;
use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\NewsCategory;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\Region;
use Modules\Site\Services\SphinxService;

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
                $input['hits'] = mt_rand(200, 500);
            }
            // 出版时间为空则设定为当前时间
            if (!isset($input['upload_at']) || empty($input['upload_at'])) {
                $input['upload_at'] = time();
            }
            if(empty($input['author'] )){
                $input['author'] = $request->user()->nickname;
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
            if(empty($input['author'] )){
                $input['author'] = $request->user()->nickname;
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
            [
                'name'  => '序号',
                'value' => 'sort',
                'type'  => '1',
            ],
            [
                'name'  => '首页显示',
                'value' => 'show_home',
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
        if ($keyword == 'status' || $keyword == 'show_home') {
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

    public function getCategoryByKeyword(Request $request) {
        try {
            $keyword = $request->keyword ?? '';
            $cate_id = $this->getCateIdByKeyWord($keyword);
            ReturnJson(true, trans('lang.request_success'), ['cate_id' => $cate_id]);
        } catch (\Exception $e) {
            ReturnJson(false, '未知错误');
        }
    }

    /**
     *
     * @param $keyword
     * @param $pageSize
     *
     */
    private function getCateIdByKeyWord($keyword) {
        if (empty($keyword)) {
            return 0;
        }
        $sphinxSrevice = new SphinxService();
        $conn = $sphinxSrevice->getConnection();
        //报告昵称,英文昵称匹配查询
        $query = (new SphinxQL($conn))->select('*')
                                      ->from('products_rt');
        $query = $query->where('status', '=', 1);
        $query = $query->where("published_date", "<", time());
        //精确搜索, 多字段匹配
        $query = $query->match(['keywords_cn',
                                'keywords',
                                'keywords_en',
                                'keywords_jp',
                                'keywords_kr',
                                'keywords_de'], '"'.$keyword.'"', true);
        $query = $query->orderBy('degree_keyword', 'asc');
        $query->setSelect('category_id');
        $result = $query->execute();
        $cateId = $result->fetchNum();
        if (empty($cateId)) {
            $cateId = 0;
        }
        if(is_array($cateId)) {
            $cateId = current($cateId);
        }
        return $cateId;
    }

    /**
     *
     * @param mixed $keyword
     * @param       $record
     *
     */
    private function matchCateId(mixed $keyword, $id): void {
        $category_id = $this->getCateIdByKeyWord($keyword);
        $upd_data = [
            'category_id' => $category_id,
        ];
        $this->ModelInstance()->where("id", $id)
             ->update($upd_data);
    }
}
