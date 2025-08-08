<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\PostSubjectStrategy;
use Modules\Site\Http\Models\PostSubjectStrategyUser;
use Modules\Site\Http\Models\ProductsCategory;

class PostSubjectStrategyController extends CrudController
{
    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    protected function list(Request $request)
    {
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
                //$model = $model->orderBy('sort', $sort)->orderBy('id', 'DESC');
                $model = $model->orderBy('id', 'DESC');
            }
            $record = $model->get()->toArray();
            if ($record) {
                $categoryData = ProductsCategory::query()->pluck("name", "id")->toArray();
                $typeList = PostSubjectStrategy::getTypeList() ?? [];
                foreach ($record as $key => $item) {
                    $record[$key]['type_name'] = $typeList[$item['type']] ?? '';

                    $itemCategoryData = explode(',', $item['category_ids']);
                    $record[$key]['category_name'] = [];
                    foreach ($itemCategoryData as $itemCategoryId) {
                        if (isset($categoryData[$itemCategoryId])) {
                            $record[$key]['category_name'][] = $categoryData[$itemCategoryId] ?? [];
                        }
                    }
                    if(!empty($record[$key]['version'])){
                        $record[$key]['version'] = explode(',', $item['version']??[]);
                    }else{
                        $record[$key]['version'] = [];
                    }

                    $record[$key]['strategy'] = [];
                    $userData = PostSubjectStrategyUser::query()->select(['user_id', 'num'])
                        ->where(['strategy_id' => $item['id']])
                        ->orderBy('sort', 'asc')
                        ->get()?->toArray();
                    $userCount = 0;
                    $postMember = array_column((new TemplateController())->getSitePostUser(), 'label', 'value');
                    if($record[$key]['type'] == PostSubjectStrategy::TYPE_ASSIGN){

                        foreach ($userData as $userItem) {
                            $member = $userItem['user_id'];
                            if (isset($postMember[$userItem['user_id']]) && !empty($postMember[$userItem['user_id']])) {
                                $member = $postMember[$userItem['user_id']];
                                $userCount = PostSubject::query()->select(['id'])
                                    ->whereIn('product_category_id', $itemCategoryData)
                                    ->where('type', PostSubject::TYPE_POST_SUBJECT)
                                    ->where('propagate_status', 0)
                                    ->where('accepter', $userItem['user_id'])
                                    ->count();
                            }
                            $record[$key]['strategy'][] = ['member' => $member, 'strategy_num' => $userItem['num'] ?? '', 'unpropagate_count' => $userCount ?? '',];
                        }
                    }elseif($record[$key]['type'] == PostSubjectStrategy::TYPE_DIMISSION){
                        
                    }
                }
            }

            $data = [
                'total'       => $total,
                'list'        => $record,
                'headerTitle' => [],
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


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
            // 课题版本
            $versionData = PostSubject::getVersionList();
            $data['version'] = [];
            foreach ($versionData as $value) {
                $data['version'][] = ['label' => $value, 'value' => $value];
            }
            
            // 领取人/发帖用户
            $data['accepter_list'] = (new TemplateController())->getSitePostUser();
            if (count($data['accepter_list']) > 0) {
                array_unshift($data['accepter_list'], ['label' => '公客', 'value' => '-1']);
            }
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * AJax单个查询
     *
     * @param $request 请求信息
     */
    protected function form(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $record = $this->ModelInstance()->findOrFail($request->id);
            $userData = PostSubjectStrategyUser::query()->select(['id', 'user_id', 'num', 'sort'])
                ->where(['strategy_id' => $record->id])
                ->orderBy('sort', 'asc')
                ->get()?->toArray();
            $record['user_data'] = $userData;
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
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

            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
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

    protected function destroy(Request $request)
    {
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

    
    /**
     * 执行策略
     *
     */
    protected function executeStrategy(Request $request){
        set_time_limit(-1);
        ini_set('memory_limit', -1);

        $input = $request->all();
        $type = $input['type'] ?? ''; // 1：获取数量;2：执行操作
        $id = $input['id'] ?? '';
        $config = PostSubjectStrategy::query()->where('id',$id)->first()->toArray();
        if ($config && $config['type'] == PostSubjectStrategy::TYPE_ASSIGN) {
            return (new PostSubjectStrategy())->assignStrategy($type, $config);
        } elseif($config && $config['type'] == PostSubjectStrategy::TYPE_DIMISSION){
            return (new PostSubjectStrategy())->dimissionStrategy($type, $config);
        }else {
            ReturnJson(false, '未知策略');
        }
        
    }
}
