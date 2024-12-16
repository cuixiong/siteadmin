<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Admin\Http\Models\DictionaryValue;
use Illuminate\Support\Facades\Validator;
use Modules\Admin\Http\Models\ListStyle;

class ProductsCategoryController extends CrudController {
    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    protected function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            if (empty($input['pid'])) {
                $input['pid'] = 0;
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
            if (empty($input['pid'])) {
                $input['pid'] = 0;
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
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
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            $data['show_home'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Show_Home_State', 'status' => 1], ['sort' => 'ASC']
            );
            $data['discount_type'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Discount_Type', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 修改分类折扣
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function discount(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            if (empty($request->discount_type)) {
                ReturnJson(false, 'discount type is empty');
            }
            if (empty($request->discount_value)) {
                ReturnJson(false, 'discount value is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $type = $request->discount_type;
            $value = $request->discount_value;
            $record->discount_type = $type;
            if ($type == 1) {
                $record->discount = $value;
                $record->discount_amount = 0;
            } elseif ($type == 2) {
                $record->discount = 100;
                $record->discount_amount = $value;
            }
            // else {
            //     throw new \Exception(trans('lang.update_error') . ':discount_type is out of range');
            // }
            //可能恢复原价
            if ($type == 1 && $value == 100) {
                $record->discount_time_begin = null;
                $record->discount_time_end = null;
            } elseif ($type == 2 && $value == 0) {
                $record->discount_time_begin = null;
                $record->discount_time_end = null;
            } else {
                $record->discount_time_begin = $request->discount_time_begin;
                $record->discount_time_end = $request->discount_time_end;
            }
            //验证
            request()->offsetSet('discount', $record->discount);
            request()->offsetSet('discount_amount', $record->discount_amount);
            $this->ValidateInstance($request);
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
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
            $search = json_decode($request->input('search'));
            list($total, $list) = (new ProductsCategory())->getAdminList('*', true, 'pid', $search);
            //表头排序
            $headerTitle = (new ListStyle())->getHeaderTitle(class_basename($ModelInstance::class), $request->user->id);
            $data = [
                'total'       => $total,
                'list'        => $list,
                'headerTitle' => $headerTitle ?? [],
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 获取某层级分类
     *
     * @param $request 请求信息
     */
    public function getCategory(Request $request) {
        $id = !empty($request->id) ? $request->id : 0;
        $data = (new ProductsCategory())->GetList(['id as value', 'name as label', 'id', 'pid'], true, 'pid',
                                                  ['pid' => $id, 'status' => 1]);
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 获取全部分类(但不包含自己以及它的分类)
     *
     * @param $request 请求信息
     */
    public function getCategoryWithoutSelf(Request $request) {
        $id = !empty($request->id) ? $request->id : 0;
        $data = (new ProductsCategory())->GetListWithoutSelf(['id as value', 'name as label', 'id', 'pid'], true, 'pid',
                                                             ['status' => 1], $id);
        ReturnJson(true, trans('lang.request_success'), $data);
    }


    /**
     * 修改热门状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeHot(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->is_hot = $request->status;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 修改推荐状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeRecommend(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->is_recommend = $request->status;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


    /**
     * 修改推荐状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeShowhome(Request $request) {
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
