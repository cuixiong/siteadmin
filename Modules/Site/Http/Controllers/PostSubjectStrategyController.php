<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\PostSubjectStrategy;
use Modules\Site\Http\Models\PostSubjectStrategyUser;
use Modules\Site\Http\Models\ProductsCategory;

class PostSubjectStrategyController extends CrudController
{

    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            $data = [];
            // 报告分类
            $data['category'] = (new ProductsCategory())->GetList(['id as value', 'name as label', 'id', 'pid'], true, 'pid', ['status' => 1]);


            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);

            // 策略类型
            $typeData = PostSubjectStrategy::getTypeList();
            $data['type'] = [];
            foreach ($typeData as $key => $value) {
                $data['type'][] = ['label' => $value, 'value' => $key];
            }

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
    
    /**
     * 新增
     *
     */
    protected function store(Request $request)
    {
        $this->ValidateInstance($request);
        $input = $request->all();

        $userData = $input['user_data'] ?? null;
        if ($userData) {
            $userData = json_decode($userData, true);
        } else {
            $userData = [];
        }

        if (empty($input['sort'])) {
            $input['sort'] = 100;
        }

        // 开启事务
        DB::beginTransaction();

        try {

            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                DB::rollBack();
                ReturnJson(false, trans('lang.add_error'));
            }

            // 新增子项
            if ($userData && is_array($userData) && count($userData) > 0) {

                $isExist = [];
                foreach ($userData as $key => $userItem) {
                    if (
                        empty($userItem['user_id']) || !($userItem['user_id'] > 0) ||
                        empty($userItem['num']) || !($userItem['num'] > 0) || in_array($userItem['user_id'], $isExist)
                    ) {
                        continue;
                    }
                    $isExist[] = $userItem['user_id'];
                    $childModel = new PostSubjectStrategyUser();
                    $userItem['strategy_id'] = $record->id;
                    $childModel->create($userItem);
                }
            }

            DB::commit();

            ReturnJson(true, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(false, $e->getMessage());
        }
    }
    /**
     * 修改
     *
     */
    protected function update(Request $request)
    {
        $this->ValidateInstance($request);
        $input = $request->all();

        $userData = $input['user_data'] ?? null;
        if ($userData) {
            $userData = json_decode($userData, true);
        } else {
            $userData = [];
        }

        if (empty($input['sort'])) {
            $input['sort'] = 100;
        }

        // 开启事务
        DB::beginTransaction();
        
        try {

            $record = $this->ModelInstance()::findOrFail($input['id']);
            if (!$record) {
                ReturnJson(FALSE, trans('lang.data_empty'));
            }

            // 删除已存在的数据
            PostSubjectStrategyUser::where('strategy_id', $record->id)->delete();

            if ($userData && is_array($userData) && count($userData) > 0) {

                $isExist = [];
                foreach ($userData as $key => $userItem) {
                    if (
                        empty($userItem['user_id']) || !($userItem['user_id'] > 0) ||
                        empty($userItem['num']) || !($userItem['num'] > 0) || in_array($userItem['user_id'], $isExist)
                    ) {
                        continue;
                    }
                    $isExist[] = $userItem['user_id'];
                    $childModel = new PostSubjectStrategyUser();
                    $userItem['strategy_id'] = $record->id;
                    $childModel->create($userItem);
                }
            }

            DB::commit();

            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(false, $e->getMessage());
        }
    }
    
    protected function destroy(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            foreach ($ids as $id) {
                $record = $this->ModelInstance()->find($id);
                if ($record) {
                    $record->delete();
                    PostSubjectStrategyUser::where('strategy_id', $record->id)->delete();
                }
            }
            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
