<?php

namespace Modules\Site\Http\Controllers;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\PostPlatform;
use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\PostSubjectLink;
use Modules\Site\Http\Models\PostSubjectLog;
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
            $model = PostSubject::from('post_subject as ps');
            $searchJson = $request->input('search');
            if (!empty($searchJson)) {
                $model = $this->ModelInstance()->getFiltersQuery($model, $searchJson);
                // ReturnJson(true, trans('lang.request_success'), $model);
            }
            if (isset($request->subjectOwn) && $request->subjectOwn == 1) {
                $model = $model->where('accepter', $request->user->id);
            }
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
                'version',
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
                'change_status',
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
                $accepterIds = array_column($record, 'accepter');
                $accepterList = User::query()->whereIn('id', $accepterIds)->pluck('nickname', 'id')->toArray();
                $platformList = PostPlatform::query()->pluck("name", "id")->toArray();
                foreach ($record as $key => $item) {
                    $record[$key]['product_category_name'] = $categoryData[$item['product_category_id']] ?? '';
                    $record[$key]['accepter_name'] = $accepterList[$item['accepter']] ?? '';
                    $record[$key]['last_propagate_time_format'] = !empty($record[$key]['last_propagate_time']) ? date('Y-m-d H:i:s', $record[$key]['last_propagate_time']) : '';
                    $record[$key]['accept_time_format'] = !empty($record[$key]['accept_time']) ? date('Y-m-d H:i:s', $record[$key]['accept_time']) : '';

                    $urlData = PostSubjectLink::query()->where(['post_subject_id' => $item['id']])->get()->toArray();
                    $urlData = array_map(function ($urlItem) use ($platformList) {
                        $urlItem['platform_name'] = $platformList[$urlItem['post_platform_id']] ?? '';
                        return $urlItem;
                    }, $urlData);
                    $record[$key]['url_data'] = $urlData;
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
     * 高级筛选
     */
    public function advancedFilters(Request $request)
    {
        $showData = [];
        $hiddenData = [];

        // if ($request->HeaderLanguage == 'en') {
        //     $field = ['english_name as label', 'value'];
        // } else {
        $field = ['name as label', 'value'];
        // }

        //当天的时间戳
        // $currentDayTimestamp = strtotime(date('Y-m-d', time())) * 1000;
        // $nextDayTimestamp = $currentDayTimestamp + 86400000 - 1000;
        // // $currentDayDate = date("D M d Y H:i:s \G\M\TO \(中国标准时间\)",$currentDayTimestamp);
        // // $nextDayDate = date("D M d Y H:i:s \G\M\TO \(中国标准时间\)",$nextDayTimestamp);
        // // return [$currentDayDate,$nextDayDate];

        // id
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $temp_filter = $this->getAdvancedFiltersItem('id', '课题ID', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        // 报告名称
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_CONTAIN, PostSubject::CONDITION_NOT_CONTAIN);
        $temp_filter = $this->getAdvancedFiltersItem('name', '报告名称', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        // product_id
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $temp_filter = $this->getAdvancedFiltersItem('product_id', '报告ID', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        // 行业
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_IN, PostSubject::CONDITION_NOT_IN);
        $options = (new ProductsCategory())->GetList(['id as value', 'name as label', 'id', 'pid'], true, 'pid', ['status' => 1]);
        $temp_filter = $this->getAdvancedFiltersItem('product_category_id', '行业', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, true, $options);
        array_push($showData, $temp_filter);

        // 分析师
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_CONTAIN, PostSubject::CONDITION_NOT_CONTAIN);
        $temp_filter = $this->getAdvancedFiltersItem('analyst', '分析师', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        // 版本
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_CONTAIN, PostSubject::CONDITION_NOT_CONTAIN);
        $temp_filter = $this->getAdvancedFiltersItem('version', '版本', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        // 宣传平台
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EXISTS_IN, PostSubject::CONDITION_EXISTS_NOT_IN);
        $options = PostPlatform::query()->select(['id as value', 'name as label'])->where('status', 1)->get()->toArray();
        $temp_filter = $this->getAdvancedFiltersItem('post_platform_id', '宣传平台', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, true, $options);
        array_push($showData, $temp_filter);

        // 宣传状态
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Propagate_State', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('propagate_status', '宣传状态', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($showData, $temp_filter);

        // 最后宣传时间
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_TIME_BETWEEN, PostSubject::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('propagate_time', '最后宣传时间', PostSubject::ADVANCED_FILTERS_TYPE_TIME, $condition);
        array_push($showData, $temp_filter);


        // 领取人/发帖用户
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_IN, PostSubject::CONDITION_NOT_IN);
        $options = (new TemplateController())->getSitePostUser();
        if (count($options) > 0) {
            array_unshift($options, ['label' => '公客', 'value' => '-1']);
        }
        $temp_filter = $this->getAdvancedFiltersItem('accepter', '领取人', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, true, $options);
        array_push($showData, $temp_filter);

        // 领取状态
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('accept_status', '领取状态', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($showData, $temp_filter);

        // 领取时间
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_TIME_BETWEEN, PostSubject::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('accept_time', '领取时间', PostSubject::ADVANCED_FILTERS_TYPE_TIME, $condition);
        array_push($showData, $temp_filter);


        /**
         * 隐藏条件
         */
        // 状态
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('status', '状态', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($hiddenData, $temp_filter);

        // 创建时间
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_TIME_BETWEEN, PostSubject::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('created_at', '创建时间', PostSubject::ADVANCED_FILTERS_TYPE_TIME, $condition);
        array_push($hiddenData, $temp_filter);

        // 修改时间
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_TIME_BETWEEN, PostSubject::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('updated_at', '修改时间', PostSubject::ADVANCED_FILTERS_TYPE_TIME, $condition);
        array_push($hiddenData, $temp_filter);


        foreach ($showData as $key => $value) {

            $showData[$key]['condition_id'] = array_column($showData[$key]['condition'], 'id', null)[0];
            // if (isset($showData[$key]['child']) && is_array($showData[$key]['child'])) {
            //     foreach ($showData[$key]['child'] as $key2 => $value2) {
            //         $showData[$key]['child'][$key2]['condition_id'] = array_column($showData[$key]['child'][$key2]['condition'], 'id', null)[0];
            //     }
            // }
        }

        foreach ($hiddenData as $key => $value) {

            $hiddenData[$key]['condition_id'] = array_column($hiddenData[$key]['condition'], 'id', null)[0];
            // if (isset($hiddenData[$key]['child']) && is_array($hiddenData[$key]['child'])) {
            //     foreach ($hiddenData[$key]['child'] as $key2 => $value2) {
            //         $hiddenData[$key]['child'][$key2]['condition_id'] = array_column($hiddenData[$key]['child'][$key2]['condition'], 'id', null)[0];
            //     }
            // }
        }

        ReturnJson(TRUE, trans('lang.request_success'),  ['showData' => $showData, 'hiddenData' => $hiddenData]);
    }

    private function getAdvancedFiltersItem($keyword, $wordTitle, $type, $condition, $multiple = false, $option = [], $timeArr = [])
    {

        $init = [
            "keyword" => '',
            "wordTitle" => "", //标题
            "type" => "", //1：普通输入框 2：下拉框 3：时间选择
            "multiple" => false,
            "condition" => [],
            "condition_id" => 0,
            "content" => "",
            "options" => [],
            "optionArr" => [], //下拉框 id-value
            "timeArr" => [],
        ];

        $temp_filter = $init;
        $temp_filter['keyword'] = $keyword;
        $temp_filter['wordTitle'] = $wordTitle;
        $temp_filter['type'] = $type;
        $temp_filter['condition'] = $condition;
        $temp_filter['multiple'] = $multiple;
        $temp_filter['options'] = $option;
        $temp_filter['timeArr'] = $timeArr;
        return $temp_filter;
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
            // $data['status'] = array_map(function($item){
            //     if(is_int($item['value'])){
            //         $item['value'] = intval($item['value']);
            //     }
            //     return $item;
            // },$data['status']);
            // 领取状态
            $data['accept_status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Accept_State', 'status' => 1], ['sort' => 'ASC']);

            // 宣传状态
            $data['propagate_status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Propagate_State', 'status' => 1], ['sort' => 'ASC']);

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

                    // 没填跳过
                    if (empty(trim($urlItem['link'] ?? ''))) {
                        continue;
                    }

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
                if (!empty($input['accepter'])) {
                    $recordUpdate['accepter'] = $input['accepter'] != -1 ? $input['accepter'] : null;
                    $recordUpdate['accept_status'] = $input['accepter'] != -1 ? 1 : 0;
                } elseif (empty($input['accepter']) && isset($request->user->id)) {
                    // 没有领取人则自己领取
                    $recordUpdate['accepter'] = $request->user->id;
                    $recordUpdate['accept_status'] = 1;
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

            $details = [];

            // 开启事务
            DB::beginTransaction();
            $model = PostSubject::findOrFail($input['id']);
            if (!$model) {
                ReturnJson(FALSE, trans('lang.data_empty'));
            }
            $this->ValidateInstance($request);
            // 记录修改前的原始数据
            $originalAttributes = $model->getAttributes();
            $res = $model->update($input);
            // 获取修改后的数据
            $changedAttributes = $model->getDirty();
            if (!$res) {
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.update_error'));
            }

            $changeData = PostSubject::getAttributesChange($originalAttributes, $changedAttributes);
            if ($changeData && count($changeData) > 0) {
                $string = '';
                foreach ($changeData as $key => $value) {
                    $string .= $key . '从' . $value['before'] . '修改成' . $value['after'] . ';';
                }
                $details[] = $string;
            }

            $postSubjectId = $model->id;

            // 最后宣传时间
            $lastPropagateTime = 0;
            // 已存在的数据
            $existLinkData = PostSubjectLink::query()->select('id', 'link', 'created_at')->where(['post_subject_id' => $postSubjectId])->get()->toArray();
            $existLinkIds = array_column($existLinkData, 'id');
            $existLinks = array_column($existLinkData, 'link');

            // 传递的Url数据
            $postLinks = array_column($urlData, 'link');

            // 对比需要删除的数据
            $deleteUrl = array_values(array_diff($existLinks, $postLinks));
            // 对比需要新增的数据
            $insertUrl = array_values(array_diff($postLinks, $existLinks));
            // 对比需要修改的数据
            $updateUrl = array_values(array_intersect($postLinks, $existLinks));

            $isInsert = false;
            $isDelete = false;
            // ReturnJson(TRUE, trans('lang.update_success'),[$postLinks,$existLinks,$deleteUrl,$insertUrl]);

            // 删除多余数据
            $deleteIds = [];
            if (count($deleteUrl) > 0) {
                $isDelete = true;
                foreach ($existLinkData as $key => $value) {
                    if (empty($value['link'])) {
                        $deleteIds[] = $value['id'];
                        continue;
                    }
                    foreach ($deleteUrl as $key2 => $value2) {

                        if ($value2 == $value['link']) {
                            $deleteIds[] = $value['id'];
                        }
                    }
                }
                // ReturnJson(TRUE, trans('lang.update_success'),$deleteIds);
                $isDelete = PostSubjectLink::query()->whereIn('id', $deleteIds)->delete();
                $isDelete = $isDelete > 0 ? true : false;
                if ($isDelete) {
                    $details[] = '删除了' . $isDelete . '个帖子';
                }
                // $deleteRecord = PostSubjectLink::query()->whereIn('id', $deletedIds)->update(['status' => 0]);
            }

            if ($insertUrl && count($insertUrl) > 0) {
                // 平台列表
                $postPlatformData = PostPlatform::query()->select(['id', 'name', 'keywords'])->where('status', 1)->get()->toArray();
                // 新增子项
                $insertCount = 0;
                foreach ($insertUrl as $urlItem) {
                    // 没填跳过
                    if (empty(trim($urlItem ?? ''))) {
                        continue;
                    }
                    // 获取平台id
                    if ($postPlatformData) {
                        foreach ($postPlatformData as $postPlatformItem) {
                            if (strpos($urlItem, $postPlatformItem['keywords']) !== false) {
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
                    $inputChild['post_subject_id'] = $model->id;
                    $inputChild['link'] = $urlItem;
                    $inputChild['post_platform_id'] = $postPlatformId;
                    $inputChild['status'] = 1;
                    $inputChild['sort'] = 100;
                    $inputChild['sort'] = 100;
                    $inputChild['success_count'] = 1;
                    $inputChild['ingore_count'] = 0;
                    $postSubjectLinkModel = new PostSubjectLink();
                    $recordChild = $postSubjectLinkModel->create($inputChild);
                    if ($recordChild) {
                        $isInsert = true;
                        $insertCount++;
                    }
                    if (!isset($recordChild) || !$recordChild) {
                        // 回滚事务
                        DB::rollBack();
                        ReturnJson(FALSE, trans('lang.update_error'));
                    }
                }
                if ($isInsert) {

                    $details[] = '宣传了' . $insertCount . '个帖子';
                }
            }

            if ($updateUrl && count($updateUrl) > 0) {

                // 取得剩余帖子的宣传时间作为最后宣传时间
                foreach ($existLinkData as $key => $value) {
                    foreach ($updateUrl as $key2 => $value2) {
                        if ($value2 == $value['link']) {
                            $value['created_at'] = !empty($value['created_at']) ? $value['created_at'] : 0;
                            $value['created_at'] = is_int($value['created_at']) ? $value['created_at'] : strtotime($value['created_at']);
                            $lastPropagateTime = $value['created_at'] > $lastPropagateTime ? $value['created_at'] : $lastPropagateTime;
                            break;
                        }
                    }
                }
            }

            // 帖子的变动需更新课题表的宣传状态等字段
            $recordUpdate = [];
            if ($isInsert || !empty($lastPropagateTime)) {
                $recordUpdate['propagate_status'] = 1;
                $recordUpdate['last_propagate_time'] = $isInsert ? time() : $lastPropagateTime;
            }
            if (!empty($input['accepter'])) {
                $recordUpdate['accept_time'] = time();
                $recordUpdate['accepter'] = $input['accepter'] != -1 ? $input['accepter'] : null;
                $recordUpdate['accept_status'] = $input['accepter'] != -1 ? 1 : 0;
            }
            if ($isInsert && empty($input['accepter'])) {
                $recordUpdate['accept_time'] = time();
                // 没有领取人则自己领取
                $recordUpdate['accepter'] = $request->user->id;
                $recordUpdate['accept_status'] = 1;
            } elseif (!$isInsert && (!$updateUrl || count($updateUrl) == 0)) {
                $recordUpdate['accept_time'] = null;
                $recordUpdate['accept_status'] = 0;
                $recordUpdate['propagate_status'] = 0;
                $recordUpdate['last_propagate_time'] = null;
            }
            if (count($recordUpdate) > 0) {
                $res = $model->update($recordUpdate);
            }
            DB::commit();

            // 添加日志
            if (count($details) > 0) {
                $log = new PostSubjectLog();
                $logData['type'] = PostSubjectLog::POST_SUBJECT_CURD;
                $logData['post_subject_id'] = $model->id;
                $logData['details'] = date('Y-m-d H:i:s', time()) . ' 【' . $request->user->nickname . '】' . "\n" . (implode("\n", $details));
                $log->create($logData);
            }

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
            $query = Products::query()->select([
                'id as product_id',
                'name',
                'category_id as product_category_id',
                'author as analyst',
                'price as version',
            ])
                ->where(['id' => $product_id])
                ->first();
            if ($query) {
                $data = $query->makeHidden((new Products())->getAppends())->toArray();
            }
        } elseif (!empty($product_name)) {

            $query = Products::query()->select([
                'id as product_id',
                'name',
                'category_id as product_category_id',
                'author as analyst',
                'price as version',
            ])
                ->where(['name' => trim($product_name)])
                ->first();
            if ($query) {
                $data = $query->makeHidden((new Products())->getAppends())->toArray();
            }
        }
        // 查看是否已有课题
        if ($data && $data['product_id']) {
            $postSubject = PostSubject::select([
                'id',
            ])
                ->where(['product_id' => $data['product_id']])
                ->first();
            if ($postSubject) {
                ReturnJson(true, trans('lang.request_success'), $postSubject);
            }
        } else {
            ReturnJson(true, trans('lang.data_empty'), false); // 报告不存在
        }

        $data['version'] = floatval($data['version']);
        ReturnJson(true, trans('lang.request_success'), $data);
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

            $record['product_category_name'] = ProductsCategory::query()->where("id", $record['product_category_id'])->value("name") ?? '';
            $record['accepter_name'] = User::query()->where('id', $record['accepter'])->value('nickname') ?? '';
            $record['last_propagate_time_format'] = !empty($record['last_propagate_time']) ? date('Y-m-d H:i:s', $record['last_propagate_time']) : '';
            $record['accept_time_format'] = !empty($record['accept_time']) ? date('Y-m-d H:i:s', $record['accept_time']) : '';

            $urlData = PostSubjectLink::query()->where(['post_subject_id' => $record['id']])->get()->toArray();
            $platformList = PostPlatform::query()->pluck("name", "id")->toArray();
            $urlData = array_map(function ($urlItem) use ($platformList) {
                $urlItem['platform_name'] = $platformList[$urlItem['post_subject_id']] ?? '';
                return $urlItem;
            }, $urlData);
            $record['url_data'] = $urlData;

            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 删除
     * @param Request $request
     */
    public function destory(Request $request)
    {
        try {
            $input = $request->all();
            $ids = $input['ids'] ?? '';
            $type = $input['type'] ?? ''; //1：获取数量;2：执行操作

            $model = PostSubject::from('post_subject as ps');
            if ($ids) {
                //选中
                $ids = explode(',', $ids);
                if (!(count($ids) > 0)) {
                    ReturnJson(true, trans('lang.param_empty') . ':ids');
                }
                $model = $model->whereIn('id', $ids);
            } else {
                //筛选
                $searchJson = $request->input('search');
                $model = $this->ModelInstance()->getFiltersQuery($model, $searchJson);
            }
            $data = [];
            if ($type == 1) {
                // 总数量
                $data['count'] = $model->count();
                ReturnJson(true, trans('lang.request_success'), $data);
            } else {

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
            }
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

        $isOwn = false;
        if (empty($accepter) && isset($request->user->id)) {
            // 没有领取人则自己领取
            $accepter = $request->user->id;
            $isOwn = true;
        } elseif (empty($accepter) && !isset($request->user->id)) {
            ReturnJson(FALSE, trans('lang.param_empty'), '未登录或缺少领取人');
        }
        if ($accepter == -1) {
            $accepterName = '公客';
        } else {
            $accepterName = User::query()->where('id', $accepter)->value('nickname');
        }


        $model = PostSubject::from('post_subject as ps');
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty') . ':ids');
            }
            $model = $model->whereIn('id', $ids);
        } else {
            //筛选
            $searchJson = $request->input('search');
            $model = $this->ModelInstance()->getFiltersQuery($model, $searchJson);
        }

        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            //查询出涉及的id
            $postSubjectData = $model->select(['id', 'name'])->get()->toArray();
            $idsData = array_column($postSubjectData, 'id');
            // 领取操作
            $updateData = [
                'accepter' => $accepter != -1 ? $accepter : null,
                'accept_time' => time(),
                'accept_status' => $accepter != -1 ? 1 : 0,
                'updated_by' => $request->user->id,
            ];
            PostSubject::query()->whereIn("id", $idsData)->update($updateData);
            // 添加日志
            // $logData = [];
            // foreach ($idsData as $key => $id) {
            //     $logDataChild = [];
            //     $logDataChild['type'] = PostSubjectLog::POST_SUBJECT_ACCEPT;
            //     $logDataChild['post_subject_id'] = $id;
            //     if ($isOwn) {
            //         $logDataChild['details'] = date('Y-m-d H:i:s', time()) . ' 【' . $accepterName . '】领取了课题';
            //     } else {
            //         $logDataChild['details'] = date('Y-m-d H:i:s', time()) . ' 【' . $request->user->nickname . '】将课题分配给【' . $accepterName . '】';
            //     }
            //     $logDataChild['created_by']= $logDataChild['updated_by'] = $request->user->id;
            //     $logDataChild['created_at'] = $logDataChild['updated_at'] = time();

            //     $logData[] = $logDataChild;
            // }

            // if (count($logData) > 0) {
            //     PostSubjectLog::insert($logData);
            // }
            $details = [];
            $acceptCount = count($idsData);
            $logData = [];
            $logData['type'] = PostSubjectLog::POST_SUBJECT_ACCEPT;
            $logData['success_count'] = $acceptCount;
            $logData['ingore_count'] = 0;
            // $logData['post_subject_id'] = ;
            if ($isOwn) {
                $details[] = date('Y-m-d H:i:s', time()) . ' 【' . $accepterName . '】领取了' . $acceptCount . '个课题';
            } else {
                $details[] = date('Y-m-d H:i:s', time()) . ' 【' . $request->user->nickname . '】将' . $acceptCount . '个课题分配给【' . $accepterName . '】';
            }
            foreach ($postSubjectData as $key => $value) {
                $details[] = '【编号' . $value['id'] . '】' . $value['name'];
            }
            $logData['details'] = implode("\n", $details);
            PostSubjectLog::create($logData);

            ReturnJson(TRUE, trans('lang.request_success'));
        }
    }

    /**
     * 导出课题
     */
    public function exportSubject(Request $request)
    {

        $input = $request->all();
        $ids = $input['ids'] ?? '';

        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作

        $model = PostSubject::from('post_subject as ps');
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty') . ':ids');
            }
            $model = $model->whereIn('id', $ids);
        } else {
            //筛选
            $searchJson = $request->input('search');
            $model = $this->ModelInstance()->getFiltersQuery($model, $searchJson);
        }

        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            // //查询出涉及的id
            // $idsData = $model->select('id')->pluck('id')->toArray();
            $subjectData = $model->select(['id', 'name', 'product_id', 'version', 'accepter'])->get()->toArray();
            if (!(count($subjectData) > 0)) {
                ReturnJson(true, trans('lang.data_empty'));
            }
        }

        $domain = env('APP_DOMAIN');
        $site = request()->header("Site");
        $date = date('Ymd', time());
        $excelHeader = [
            '课题',
            '版本',
            '快速搜索链接',
            '发贴链接',
        ];

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser('export-topic-' . count($subjectData) . '-' . $date . '.xlsx'); // 将文件输出到浏览器并下载

        $accepter = $subjectData[0]['accepter'] ?? 0;
        if (!empty($accepter)) {
            $sheetName = User::query()->where('id', $accepter)->value('nickname') ?? 'Sheet1';
        } else {
            $sheetName = $request->user->nickname ?? 'Sheet1';
        }
        $writer->getCurrentSheet()->setName($sheetName);

        // 添加标题
        $rowData = WriterEntityFactory::createRowFromArray($excelHeader);
        $writer->addRow($rowData);

        $details = [];
        foreach ($subjectData as $key => $subject) {
            $rowData = [];
            $rowData[] = $subject['name'];
            $rowData[] = $subject['version'];
            // https://siteadmin.marketmonitorglobal.com.cn/#/gircn/products/fastList?type=id&keyword=2124513
            $rowData[] = $domain . '/#/' . $site . '/products/fastList?type=id&keyword=' . $subject['product_id'];
            $rowData[] = '';

            $rowData = WriterEntityFactory::createRowFromArray($rowData);
            $writer->addRow($rowData);
            $details[] = '【课题编号' . $subject['id'] . '】' . $subject['name'];
        }
        $writer->close();

        // exit;
        $exportCount = count($subjectData);

        if ($exportCount) {
            $logData = [];
            $logData['type'] = PostSubjectLog::POST_SUBJECT_EXPORT;
            $logData['success_count'] = $exportCount;
            $logData['ingore_count'] = 0;
            // $logData['post_subject_id'] = ;
            $logData['details'] = date('Y-m-d H:i:s', time()) . ' 【' . $request->user->nickname . '】导出了' . $exportCount . '个课题' . "\n" . (implode("\n", $details));
            $logData['details'] .= implode("\n", $details);
            PostSubjectLog::create($logData);
        }
        exit;
    }


    /**
     * 导出日志(课题含帖子)
     */
    public function exportSubjectLink(Request $request)
    {

        $input = $request->all();
        $ids = $input['ids'] ?? '';

        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作

        $model = PostSubject::from('post_subject as ps');
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty') . ':ids');
            }
            $model = $model->whereIn('id', $ids);
        } else {
            //筛选
            $searchJson = $request->input('search');
            $model = $this->ModelInstance()->getFiltersQuery($model, $searchJson);
        }

        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            // //查询出涉及的id
            // $idsData = $model->select('id')->pluck('id')->toArray();
            $subjectData = $model->select(['id', 'name', 'product_id', 'version', 'accepter'])->get()->toArray();
            if (!(count($subjectData) > 0)) {
                ReturnJson(true, trans('lang.data_empty'));
            }
        }


        // 查询帖子
        $subjectIds = array_column($subjectData, 'id');
        $subjectLinkData = PostSubjectLink::query()->select([
            'id',
            'post_subject_id',
            'post_platform_id',
            'link',
        ])
            ->whereIn("post_subject_id", $subjectIds)
            ->get()
            ->toArray();
        $subjectLinkGroup = [];
        foreach ($subjectLinkData as $key => $item) {
            $postSubjectId = $item['post_subject_id'];
            // $postSubjectLinkId = $item['id'];
            if (!empty($postSubjectId) && !isset($subjectLinkGroup[$postSubjectId])) {
                $subjectLinkGroup[$postSubjectId] = [];
            }
            $subjectLinkGroup[$postSubjectId][] = $item['link'];
        }
        // 课题按领取人分组
        $subjectGroup = [];
        foreach ($subjectData as $key => $item) {
            $subjectAccepterId = $item['accepter'] ?? 0;
            if (!empty($subjectAccepterId) && !isset($subjectGroup[$subjectAccepterId])) {
                $subjectGroup[$subjectAccepterId] = [];
            }
            $subjectGroup[$subjectAccepterId][] = $item;
        }


        // 领取人列表
        $accepterIds = array_column($subjectData, 'accepter');
        $accepterList = User::query()->whereIn('id', $accepterIds)->pluck('nickname', 'id')->toArray();


        $domain = env('APP_DOMAIN');
        $site = request()->header("Site");
        $date = date('Ymd', time());
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser('import-posts-' . count($subjectData) . '-' . $date . '.xlsx'); // 将文件输出到浏览器并下载

        // 循环课题，输出excel
        $excelHeader = [
            '课题',
            '版本',
            '快速搜索链接',
            '发贴链接',
        ];
        $details = [];
        $subjectSuccess = 0;
        $subjectLinkSuccess = 0;
        $subjectFail = 0;
        $subjectLinkFail = 0;
        $firstSheetName = '';
        foreach ($subjectGroup as $groupAccepterId => $subjectGroupItem) {
            // 按每个领取人分不同的工作簿
            $sheetName = $accepterList[$groupAccepterId] ?? '';
            if (empty($sheetName)) {
                $subjectFail++;
                $subjectLinkFail += count($subjectGroupItem);
                $details[] = '【领取人ID' . $groupAccepterId . '不存在】';
                foreach ($subjectGroupItem as $key => $value) {
                    $details[] = '--【编号' . $value['id'] . '】' . $value['name'];
                }
                continue;
            }
            // 确认是否第一个工作簿
            if (empty($firstSheetName)) {
                $firstSheetName = $sheetName;
            }
            if ($firstSheetName == $sheetName) {
                $writer->getCurrentSheet()->setName($sheetName);
            } else {
                $writer->addNewSheetAndMakeItCurrent()->setName($sheetName);
            }

            // 添加标题
            $rowData = WriterEntityFactory::createRowFromArray($excelHeader);
            $writer->addRow($rowData);
            foreach ($subjectGroupItem as $key => $subject) {

                $rowData = [];
                $rowData[] = $subject['name'];
                $rowData[] = $subject['version'];
                // https://siteadmin.marketmonitorglobal.com.cn/#/gircn/products/fastList?type=id&keyword=2124513
                $rowData[] = $domain . '/#/' . $site . '/products/fastList?type=id&keyword=' . $subject['product_id'];
                if (isset($subjectLinkGroup[$subject['id']]) && is_array($subjectLinkGroup[$subject['id']]) && count($subjectLinkGroup[$subject['id']]) > 0) {
                    $subjectSuccess++;
                    foreach ($subjectLinkGroup[$subject['id']] as $LinkIndex => $linkValue) {
                        $tempRowData = $rowData;
                        if ($LinkIndex != 0) {
                            $tempRowData = array_map(function ($item) {
                                return "";
                            }, $tempRowData);
                        }
                        $tempRowData[] = !empty($linkValue) ? $linkValue : "";
                        $tempRowData = WriterEntityFactory::createRowFromArray($tempRowData);
                        $writer->addRow($tempRowData);
                        $subjectLinkSuccess++;
                    }
                }
            }
        }
        // return json_encode($a);

        $writer->close();

        $exportCount = count($subjectData);
        if ($exportCount) {
            $logData = [];
            $logData['type'] = PostSubjectLog::POST_SUBJECT_LINK_EXPORT;
            // $logData['post_subject_id'] = ;
            $logData['success_count'] = $subjectSuccess;
            $logData['ingore_count'] = $subjectFail;
            $logData['details'] = '';
            $logData['details'] .= date('Y-m-d H:i:s', time()) . ' 【' . $request->user->nickname . '】';
            $logData['details'] .= '成功导出' . $subjectSuccess . '个课题, ' . $subjectLinkSuccess . '个链接, ';
            $logData['details'] .= '有' . $subjectFail . '个课题, ' . $subjectLinkFail . '个链接导出失败' . "\n";
            $logData['details'] .= implode("\n", $details);
            PostSubjectLog::create($logData);
        }

        exit;
    }


    /**
     * 上传日志(帖子)
     */
    public function uploadSubjectLink(Request $request)
    {

        // $time1 = microtime(true);
        $file_temp_name = $_POST['file_temp_name'] ?? null; //随机数，用于建立临时文件夹
        $chunks = $_POST['totalNo'] ?? null; //切片总数
        $currentChunk = $_POST['no'] ?? null; //当前切片
        // $file_real_name = $_POST['fileName'] ?? null; //文件名
        // $file_full_size = $_POST['file_full_size'] ?? null;//文件总大小
        // $file_type = $_POST['file_type'] ?? null;//文件类型
        $blob = $_FILES['file'] ?? null; //二进制数据


        if ($file_temp_name === null) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少随机数');
        } elseif ($chunks === null) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少切片总数');
        } elseif ($currentChunk === null) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少当前切片序号');
        }
        // elseif ($file_real_name === null) {
        //     ReturnJson(FALSE, trans('lang.param_empty'), '缺少文件名');
        // } 
        elseif ($blob === null) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少blob数据');
        }

        $blob = $_FILES['file'];
        // /www/wwwroot/yadmin/admin/public/site/gircn/exportDir
        $basePath = public_path();
        $dir = $basePath . '/site/' . $request->header('Site') . '/post-subject/';
        $dirtemp = $basePath . '/site/' . $request->header('Site') . '/post-subject/temp/'; // 保存分片文件的目录

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!is_dir($dirtemp)) {
            mkdir($dirtemp, 0777, true);
        }

        $dirtempfile = $dirtemp . $file_temp_name;

        if (!is_dir($dirtempfile)) {
            mkdir($dirtempfile, 0777, true);
        }

        $baseFileName = $dirtempfile . '/' . $file_temp_name;

        move_uploaded_file($blob['tmp_name'], $baseFileName . '_' . $currentChunk);

        // sleep(mt_rand(1, 3)); // 随机暂停，方式上传速度过快无法合并文件

        if (count(glob($baseFileName . '_*')) != $chunks) {
            ReturnJson(true, trans('lang.request_success'), 'success');
            // return 'success';
        }
        // sleep(mt_rand(1, 3)); // 随机暂停，方式上传速度过快无法合并文件

        // 合并文件
        $file_real_name = $file_temp_name . '-' . time();
        $excelPath = $dir . '/' . $file_real_name;
        $butffer = '';
        for ($i = 1; $i <= $chunks; $i++) {
            $sliceFileName = $baseFileName . '_' . $i;
            if (!is_file($sliceFileName)) {
                ReturnJson(FALSE, trans('lang.request_error'), '切片文件缺失导致合并失败');
            }
            // $butffer = file_get_contents($sliceFileName); // 这种每次读取后就立即写入文件，速度慢一点，但内存消耗更少
            // file_put_contents($excelPath, $butffer, FILE_APPEND);
            $butffer .= file_get_contents($sliceFileName); // 这种一次读取完然后再写入文件速度快一点，但更消耗内存
        }
        file_put_contents($excelPath, $butffer); // 这种一次读取完然后再写入文件速度快一点，但更消耗内存
        // 删除分片文件和文件夹
        array_map('unlink', glob($dirtempfile . '/*'));
        rmdir($dirtempfile);

        // 开始读取文件
        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", '-1'); // 不限制内存
        // return ['code' => 1, 'msg' => $excelPath];
        if (!is_file($excelPath)) {
            ReturnJson(FALSE, trans('lang.request_error'), '合并文件不存在');
            // throw new \yii\web\BadRequestHttpException($excelPath . '_' . $cachekey);
        }
        // ReturnJson(true, trans('lang.request_error'),  microtime(true)- $time1);

        $reader = ReaderEntityFactory::createXLSXReader($excelPath);
        $reader->setShouldPreserveEmptyRows(false);
        $reader->setShouldFormatDates(true);
        $reader->open($excelPath);
        $excelData = [];

        $postPlatformData = PostPlatform::query()->select(['id', 'name', 'keywords'])->where('status', 1)->get()->toArray();

        $details = [];
        $failDetails = [];
        $subjectSuccess = 0;
        // $subjectLinkSuccess = 0;
        $subjectFail = 0;
        // $subjectLinkFail = 0;

        foreach ($reader->getSheetIterator() as $sheetKey => $sheet) {
            $sheetName = $sheet->getName();
            // 查询用户
            $accepter = User::query()->where('nickname', $sheetName)->value('id');
            if (!$accepter) {
                // $subjectFail++;
                $failDetails[] = '【' . $sheetName . '】领取人' . $sheetName . '不存在';
                // 
                $subjectFail += iterator_count($sheet->getRowIterator()) ?? 0;
                continue;
            }
            $excelData[$sheetName] = [];
            $prevProductId = 0;
            foreach ($sheet->getRowIterator() as $rowKey => $sheetRow) {

                if ($rowKey == 1) {
                    continue;
                }
                $tempRow = $sheetRow->toArray();
                $fastLink = $tempRow[2] ?? '';
                $postLink = $tempRow[3] ?? '';

                if (empty($fastLink) && !empty($prevProductId)) {
                    $productId = $prevProductId;
                } elseif (!empty($fastLink) && preg_match('/[?&]keyword=([^&]+)/', $fastLink, $matches)) {
                    $productId = $prevProductId = $matches[1];
                } else {
                    $subjectFail++;
                    $failDetails[] = '【' . $sheetName . '】第' . $rowKey . '行，缺少快速搜索链接';
                    continue;
                }
                if (empty($productId) || empty($postLink)) {
                    $subjectFail++;
                    $failDetails[] = '【' . $sheetName . '】第' . $rowKey . '行，无法提取报告id或发帖链接未填写';
                    continue;
                }

                $excelData[$sheetName][$prevProductId][] = $postLink;
            }
            // 处理每个工作簿的数据
            foreach ($excelData[$sheetName] as $productId => $postLinkGroup) {
                // if (!$postLinkGroup || count($postLinkGroup) == 0) {
                //     // 没有链接数据,跳过
                //     continue;
                // }

                $postSubjectData = PostSubject::query()->select(['id', 'accepter'])->where("product_id", $productId)->first()?->toArray();

                if (!$postSubjectData) {
                    // 查不到该课题,跳过
                    $subjectFail += count($postLinkGroup);
                    $failDetails[] = '【' . $sheetName . '】查不到报告id为' . $productId . '的课题';
                    continue;
                }
                if ($accepter != $postSubjectData['accepter']) {
                    // 领取人不一致,跳过
                    $subjectFail += count($postLinkGroup);
                    $failDetails[] = '【' . $sheetName . '】【课题id-' . $postSubjectData['id'] . '】课题领取者不一致';
                    continue;
                }

                $urlData = [];
                $urlData = PostSubjectLink::query()->select(['link'])->where(['post_subject_id' => $postSubjectData['id']])->pluck('link')?->toArray() ?? [];
                $isUpdate = false;
                foreach ($postLinkGroup as $postLinkValue) {
                    if (in_array($postLinkValue, $urlData)) {
                        // 链接一致不变动
                        $subjectFail++;
                        $failDetails[] = '【' . $sheetName . '】【课题id-' . $postSubjectData['id'] . '】' . $postLinkValue . ' 链接已存在';
                        continue;
                    } else {
                        // 获取平台id
                        if ($postPlatformData) {
                            foreach ($postPlatformData as $postPlatformItem) {
                                if (strpos($postLinkValue, $postPlatformItem['keywords'])) {
                                    $postPlatformId = $postPlatformItem['id'];
                                    break;
                                }
                            }
                        } else {
                            continue;
                        }
                        if (!isset($postPlatformId) || empty($postPlatformId)) {
                            $subjectFail++;
                            $failDetails[] = '【' . $sheetName . '】【课题id' . $postSubjectData['id'] . '】' . $postLinkValue . ' 没有对应平台';
                            continue;
                        }
                        // 新增
                        $insertChild = [];
                        $insertChild['post_subject_id'] = $postSubjectData['id'];
                        $insertChild['link'] = $postLinkValue;
                        $insertChild['post_platform_id'] = $postPlatformId;
                        $insertChild['status'] = 1;
                        $insertChild['sort'] = 100;
                        $postSubjectLinkModel = new PostSubjectLink();
                        $recordChild = $postSubjectLinkModel->create($insertChild);
                        if ($recordChild) {
                            $subjectSuccess++;
                            $isUpdate = true;
                        }
                    }
                }
                // 如果新增了链接，更新课题时间
                if ($isUpdate) {
                    $recordUpdate = [];
                    $recordUpdate['propagate_status'] = 1;
                    $recordUpdate['last_propagate_time'] = time();
                    $recordUpdate['accept_time'] = time();
                    $recordUpdate['accept_status'] = 1;
                    $recordUpdate['accepter'] = $accepter;
                    PostSubject::query()->where("id", $postSubjectData['id'])->update($recordUpdate);
                }
            }
        }

        $logData = [];
        $logData['type'] = PostSubjectLog::POST_SUBJECT_LINK_UPLOAD;
        // $logData['post_subject_id'] = ;
        $logData['success_count'] = $subjectSuccess;
        $logData['ingore_count'] = $subjectFail;
        $logData['details'] = '';
        $logData['details'] .= date('Y-m-d H:i:s', time()) . ' 【' . $request->user->nickname . '】';
        $logData['details'] .= '成功导入' . $subjectSuccess . '个链接';
        $logData['details'] .= '，有' . $subjectFail . '个链接导入失败' . "\n";
        $logData['details'] .= implode("\n", $failDetails);
        PostSubjectLog::create($logData);

        if (!$excelData || count($excelData) < 1) {
            ReturnJson(FALSE, trans('lang.data_empty'), '没数据');
        }
        ReturnJson(true, trans('lang.request_success'));
    }


    /**
     * 课题操作记录
     */
    public function postSubjectLog(Request $request)
    {
        $input = $request->all();
        $id = $input['id'] ?? '';
        if (empty($id)) {
            ReturnJson(true, trans('lang.param_empty'));
        }
        $data = PostSubjectLog::query()->select(['id', 'created_at', 'details'])->where('post_subject_id', $id)->get()?->toArray();
        if ($data) {
            foreach ($data as $key => $item) {
                $data[$key]['details'] = !empty($item['details']) ? explode("\n", $item['details']) : [];
            }
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }
}
