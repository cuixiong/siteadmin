<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\PostPlatform;
use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\PostSubjectLink;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;

class PostSubjectController extends CrudController
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
            $fields = [
                'id',
                'name',
                'product_id',
                'product_category_id',
                'analyst',
                'status',
                'sort',
                'created_at',
                'created_by',
                'updated_at',
                'updated_by',
                'last_propagate_time',
                'propagate_status',
                'accepter',
                'accept_time',
                'accept_status',
            ];
            $model = $model->select($fields);
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
                $categoryIds = array_column($record, 'product_category_id');
                $categoryData = ProductsCategory::query()->whereIn("id", $categoryIds)->pluck("name", "id")->toArray();
                foreach ($record as $key => $item) {
                    $record[$key]['product_category_name'] = $categoryData[$item['product_category_id']] ?? '';
                    $record[$key]['last_propagate_time_format'] = !empty($record[$key]['last_propagate_time']) ? date('Y-m-d H:i:s', $record[$key]['last_propagate_time']) : '';
                    $record[$key]['accept_time_format'] = !empty($record[$key]['accept_time']) ? date('Y-m-d H:i:s', $record[$key]['accept_time']) : '';
                    $record[$key]['url_data'] = PostSubjectLink::query()->where(['post_subject_id' => $item['id']])->get()->toArray();
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

            // 领取状态
            $data['accept_status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Accept_State', 'status' => 1], ['sort' => 'ASC']);

            // 宣传状态
            $data['propagate_status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Propagate_State', 'status' => 1], ['sort' => 'ASC']);

            // 领取人/发帖用户
            $data['accepter_list'] = (new TemplateController())->getSitePostUser();


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
        try {
            $this->ValidateInstance($request);
            $input = $request->all();

            $urlData = $input['url_data'] ?? null;
            if ($urlData) {
                $urlData = json_decode($urlData, true);
            } else {
                $urlData = [];
            }

            if (empty($input['sort'])) {
                $input['sort'] = 100;
            }
            // 开启事务
            DB::beginTransaction();

            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                DB::rollBack();
                ReturnJson(false, trans('lang.add_error'));
            }
            // 新增子项
            $hasChild = false;
            if ($urlData && is_array($urlData) && count($urlData) > 0) {
                $postPlatformData = PostPlatform::query()->select(['id', 'name', 'keywords'])->where('status', 1)->get()->toArray();

                foreach ($urlData as $key => $urlItem) {


                    if ($postPlatformData) {

                        foreach ($postPlatformData as $postPlatformItem) {
                            if (strpos($urlItem['link'], $postPlatformItem['keywords'])) {
                                $postPlatformId = $postPlatformItem['id'];
                                break;
                            }
                        }
                    } else {
                        continue;
                    }
                    if (!isset($postPlatformId) || empty($postPlatformId)) {
                        continue;
                    }

                    $inputChild = [];
                    $inputChild['post_subject_id'] = $record->id;
                    $inputChild['link'] = $urlItem['link'];
                    $inputChild['post_platform_id'] = $postPlatformId;
                    $inputChild['status'] = 1;
                    $inputChild['sort'] = 100;

                    $postSubjectLinkModel = new PostSubjectLink();
                    $recordChild = $postSubjectLinkModel->create($inputChild);
                    if ($recordChild) {
                        $hasChild = true;
                    }
                }
            }
            if ($hasChild) {
                // 如果有添加课题链接
                $recordUpdate = [];
                $recordUpdate['propagate_status'] = 1;
                $recordUpdate['last_propagate_time'] = time();
                $recordUpdate['accept_time'] = time();
                $recordUpdate['accept_status'] = 1;
                if (!empty($input['accepter'])) {
                    $recordUpdate['accepter'] = $input['accepter'];
                } elseif (empty($input['accepter']) && isset($request->user->id)) {
                    // 没有领取人则自己领取
                    $recordUpdate['accepter'] = $request->user->id;
                }

                $res = $record->update($recordUpdate);
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
        try {

            $input = $request->all();

            $urlData = $input['url_data'] ?? null;
            if ($urlData) {
                $urlData = json_decode($urlData, true);
            } else {
                $urlData = [];
            }

            // 开启事务
            DB::beginTransaction();
            $model = new PostSubject();
            $model = $model->findOrFail($input['id']);
            if (!$model) {
                ReturnJson(FALSE, trans('lang.data_empty'));
            }
            $this->ValidateInstance($request);
            $res = $model->update($input);
            if (!$res) {
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.update_error'));
            }

            $postSubjectId = $model->id;
            // 已存在的数据
            $existLinkIds = PostSubjectLink::query()->select('id')->where(['post_subject_id' => $postSubjectId])->pluck('id')->toArray();

            // 需要删除的数据
            $updateUrlIds = [];
            foreach ($urlData as $item) {
                if (isset($item['id']) && !empty($item['id'])) {
                    array_push($updateUrlIds, $item['id']);
                }
            }
            $deletedIds = array_values(array_diff($existLinkIds, $updateUrlIds));

            // 删除多余数据
            if (count($deletedIds) > 0) {
                // $deleteRecord = PostSubjectLink::query()->whereIn('id', $deletedIds)->update(['status' => 0]);
                PostSubjectLink::query()->whereIn('id', $deletedIds)->delete();
            }
            // 修改或新增子项
            $postPlatformData = PostPlatform::query()->select(['id', 'name', 'keywords'])->where('status', 1)->get()->toArray();
            
            $hasChild = false;
            $isChange = false;
            foreach ($urlData as $urlItem) {
                // 获取平台id
                if ($postPlatformData) {
                    foreach ($postPlatformData as $postPlatformItem) {
                        if (strpos($urlItem['link'], $postPlatformItem['keywords'])) {
                            $postPlatformId = $postPlatformItem['id'];
                            break;
                        }
                    }
                } else {
                    continue;
                }
                if (!isset($postPlatformId) || empty($postPlatformId)) {
                    continue;
                }

                $isExist = isset($urlItem['id']) && !empty($urlItem['id']);
                // 修改
                if ($isExist) {
                    $itemModel = PostSubjectLink::find($urlItem['id']);
                    if ($itemModel && ($itemModel->link != $urlItem['link'] || $itemModel->post_platform_id != $postPlatformId)) {
                        $itemModel->link = $urlItem['link'];
                        $itemModel->post_platform_id = $postPlatformId;
                        $recordChild = $itemModel->update();
                        $isChange = true;
                    } else {
                        $isExist = false;
                    }
                }
                // 新增
                if (!$isExist) {

                    $inputChild = [];
                    $inputChild['post_subject_id'] = $model->id;
                    $inputChild['link'] = $urlItem['link'];
                    $inputChild['post_platform_id'] = $postPlatformId;
                    $inputChild['status'] = 1;
                    $inputChild['sort'] = 100;
                    $postSubjectLinkModel = new PostSubjectLink();
                    $recordChild = $postSubjectLinkModel->create($inputChild);
                    if ($recordChild) {
                        $hasChild = true;
                    }
                }

                


                if (!isset($recordChild) || !$recordChild) {
                    // 回滚事务
                    DB::rollBack();
                    ReturnJson(FALSE, trans('lang.update_error'));
                }
            }
            
            // 帖子的变动需更新课题表的宣传状态等字段
            $recordUpdate = [];
            if ($hasChild || $isChange){
                $recordUpdate['propagate_status'] = 1;
                $recordUpdate['last_propagate_time'] = time();
            }
            if ($hasChild && empty($model->accepter)) {
                $recordUpdate['accept_time'] = time();
                $recordUpdate['accept_status'] = 1;
                if (!empty($input['accepter'])) {
                    $recordUpdate['accepter'] = $input['accepter'];
                } elseif (empty($input['accepter']) && isset($request->user->id)) {
                    // 没有领取人则自己领取
                    $recordUpdate['accepter'] = $request->user->id;
                }

            }
            if(count($recordUpdate)>0){
                $res = $model->update($recordUpdate);
            }
            DB::commit();

            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 根据id查询报告名称、分类、作者(分析师)
     *
     */
    protected function getProductInfo(Request $request)
    {

        $input = $request->all();
        $product_id = $input['product_id'] ?? '';
        $product_name = $input['name'] ?? '';
        $data = [];
        if (!empty($product_id)) {
            $data = Products::query()->select([
                'id as product_id',
                'name',
                'category_id as product_category_id',
                'author as analyst',
            ])
                ->where(['id' => $product_id])
                ->first()
                ->makeHidden((new Products())->getAppends())
                ->toArray();
        } elseif (!empty($product_name)) {

            $data = Products::query()->select([
                'id as product_id',
                'name',
                'category_id as product_category_id',
                'author as analyst',
            ])
                ->where(['name' => trim($product_name)])
                ->first()
                ->makeHidden((new Products())->getAppends())
                ->toArray();
        }

        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 删除
     * @param Request $request
     */
    public function destory(Request $request)
    {
        try {
            if (empty($request->ids)) {
                ReturnJson(FALSE, '请输入需要删除的ID');
            }
            DB::beginTransaction();
            $record = $this->ModelInstance()->query();
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            $rs = $record->whereIn('id', $ids)->delete();
            if (!$rs) {
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.delete_error'));
            }
            //删除子项
            PostSubjectLink::whereIn('post_subject_id', $ids)->delete();

            DB::commit();
            ReturnJson(TRUE, trans('lang.delete_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 修改状态
     * @param $request 请求信息
     * @param $id 主键ID
     */
    public function changeStatus(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(FALSE, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->status = $request->status;
            if (!$record->save()) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 领取/分配
     */
    public function accept(Request $request)
    {
        // set_time_limit(-1);
        // ini_set('memory_limit', -1);
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作
        $accepter = $input['accepter'] ?? '';
        if (empty($accepter) && isset($request->user->id)) {
            // 没有领取人则自己领取
            $accepter = $request->user->id;
        } else {
            ReturnJson(FALSE, trans('lang.param_empty'), '未登录或缺少领取人');
        }

        $ModelInstance = $this->ModelInstance();
        $model = $ModelInstance->query();
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty') . ':ids');
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
            //查询出涉及的id
            $idsData = $model->select('id')->pluck('id')->toArray();

            // 领取操作
            $updateData = [
                'accepter' => $accepter,
                'accept_time' => time(),
                'accept_status' => 1
            ];
            PostSubject::query()->whereIn("id", $idsData)->update($updateData);
            ReturnJson(TRUE, trans('lang.request_success'));
        }
    }
}
