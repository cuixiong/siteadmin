<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\AutoPostConfig;
use Modules\Site\Http\Models\AutoPostLog;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;

class AutoPostLogController extends CrudController {
    public function searchDroplist(Request $request) {
        try {
            // 状态开关
            $postStatusList = AutoPostLog::$postStatus;
            $postStatusSelect = [];
            foreach ($postStatusList as $key => $value){
                $postStatusSelect[] = [
                    'value' => $key,
                    'label' => $value
                ];
            }
            $data['post_log_status_list'] = $postStatusSelect;

            //代号
            $auto_post_config_list = AutoPostConfig::query()->where("status" , 1)->selectRaw('id as value , code as label')->get()->toArray();
            $data['auto_post_config_list'] = $auto_post_config_list;

            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


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
                $model = $model->orderBy('created_at', 'DESC');
            }
            $record = $model->get()->toArray();
            $product_id_list = array_column($record, 'product_id');
            $productList = Products::query()->whereIn('id', $product_id_list)->select(['id', 'name' , 'url'])->get()->keyBy('id')->toArray();

            $tempList = Template::query()->where("status" , 1)->pluck('name' , 'id')->toArray();

            $domain = getSiteDomain();
            foreach ($record as &$item){
                //状态
                $item['post_status_text'] = AutoPostLog::$postStatus[$item['post_status']];

                //报告信息
                $product_info = $productList[$item['product_id']];
                if (!empty($product_info['url'])) {
                    $url = $domain."/reports/{$item['product_id']}/{$product_info['url']}";
                } else {
                    $url = $domain."/reports/{$item['product_id']}";
                }
                $product_info['url'] = $url;
                $item['product_info'] = $product_info;
                $item['title_temp_name'] = $tempList[$item['title_template_id']] ?? '';
                $item['content_temp_name'] = $tempList[$item['content_template_id']] ?? '';
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


}
