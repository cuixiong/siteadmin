<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\PostPlatform;
use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\PostSubjectArticle;
use Modules\Site\Http\Models\PostSubjectArticleLink;
use Modules\Site\Http\Models\PostSubjectLog;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PostSubjectArticleController extends CrudController
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
            $model = PostSubjectArticle::from('post_subject_article as ps');
            $searchJson = $request->input('search');

            $subjectOwnId = NULL;
            if (isset($request->subjectOwn) && $request->subjectOwn == 1) {
                $subjectOwnId = -1;
            } elseif (isset($request->subjectOwn) && $request->subjectOwn == 2) {
                $subjectOwnId = $request->user->id;
            }
            $model = $this->ModelInstance()->getFiltersQuery($model, $searchJson, $subjectOwnId);
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
                'keywords',
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
                $accepterIds = array_column($record, 'accepter');
                $accepterList = User::query()->whereIn('id', $accepterIds)->pluck('nickname', 'id')->toArray();
                $platformList = PostPlatform::query()->pluck("name", "id")->toArray();
                foreach ($record as $key => $item) {
                    $record[$key]['accepter_name'] = $accepterList[$item['accepter']] ?? '';
                    $record[$key]['last_propagate_time_format'] = !empty($record[$key]['last_propagate_time']) ? date('Y-m-d H:i:s', $record[$key]['last_propagate_time']) : '';
                    $record[$key]['accept_time_format'] = !empty($record[$key]['accept_time']) ? date('Y-m-d H:i:s', $record[$key]['accept_time']) : '';

                    $urlData = PostSubjectArticleLink::query()->where(['post_subject_id' => $item['id']])->get()->toArray();
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

        // 报告名称
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_CONTAIN, PostSubjectArticle::CONDITION_NOT_CONTAIN);
        $temp_filter = $this->getAdvancedFiltersItem('name', '报告名称', PostSubjectArticle::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_CONTAIN, PostSubjectArticle::CONDITION_NOT_CONTAIN);
        $temp_filter = $this->getAdvancedFiltersItem('keywords', '关键词', PostSubjectArticle::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        // 领取人/发帖用户
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_IN, PostSubjectArticle::CONDITION_NOT_IN);
        $options = (new TemplateController())->getSitePostUser();
        if (count($options) > 0) {
            array_unshift($options, ['label' => '公客', 'value' => '-1']);
        }
        $temp_filter = $this->getAdvancedFiltersItem('accepter', '领取人', PostSubjectArticle::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, true, $options);
        array_push($showData, $temp_filter);

        // 领取状态
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_EQUAL, PostSubjectArticle::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Accept_State', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('accept_status', '领取状态', PostSubjectArticle::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($showData, $temp_filter);

        // 领取时间
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_TIME_BETWEEN, PostSubjectArticle::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('accept_time', '领取时间', PostSubjectArticle::ADVANCED_FILTERS_TYPE_TIME, $condition);
        array_push($showData, $temp_filter);

        // 最后宣传时间
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_TIME_BETWEEN, PostSubjectArticle::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('last_propagate_time', '最后宣传时间', PostSubjectArticle::ADVANCED_FILTERS_TYPE_TIME, $condition);
        array_push($showData, $temp_filter);


        // 宣传平台
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_EXISTS_IN, PostSubjectArticle::CONDITION_EXISTS_NOT_IN);
        $options = PostPlatform::query()->select(['id as value', 'name as label'])->where('status', 1)->get()->toArray();
        $temp_filter = $this->getAdvancedFiltersItem('post_platform_id', '宣传平台', PostSubjectArticle::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, true, $options);
        array_push($showData, $temp_filter);


        /**
         * 隐藏条件
         */

        // id
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_EQUAL, PostSubjectArticle::CONDITION_NOT_EQUAL);
        $temp_filter = $this->getAdvancedFiltersItem('id', '课题ID', PostSubjectArticle::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($hiddenData, $temp_filter);


        // 宣传状态
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_EQUAL, PostSubjectArticle::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Propagate_State', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('propagate_status', '宣传状态', PostSubjectArticle::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($hiddenData, $temp_filter);


        // 状态
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_EQUAL, PostSubjectArticle::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('status', '状态', PostSubjectArticle::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($hiddenData, $temp_filter);

        // 创建时间
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_TIME_BETWEEN, PostSubjectArticle::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('created_at', '创建时间', PostSubjectArticle::ADVANCED_FILTERS_TYPE_TIME, $condition);
        array_push($hiddenData, $temp_filter);

        // 修改时间
        $condition = PostSubjectArticle::getFiltersCondition(PostSubjectArticle::CONDITION_TIME_BETWEEN, PostSubjectArticle::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('updated_at', '修改时间', PostSubjectArticle::ADVANCED_FILTERS_TYPE_TIME, $condition);
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

            // 是否有cagr数据
            // $data['propagate_status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Has_Cagr', 'status' => 1], ['sort' => 'ASC']);

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
                            if (strpos($urlItem['link'], $postPlatformItem['keywords']) !== false) {
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

                    $postSubjectLinkModel = new PostSubjectArticleLink();
                    $recordChild = $postSubjectLinkModel->create($inputChild);
                    if ($recordChild) {
                        $hasChild = true;
                    }
                }
            }
            $recordUpdate = [];
            if ($hasChild) {
                // 如果有添加课题链接
                $recordUpdate['propagate_status'] = 1;
                $recordUpdate['last_propagate_time'] = time();
            }
            // 新增的无论怎样都要领取
            if (!empty($input['accepter'])) {
                $recordUpdate['accepter'] = $input['accepter'] != -1 ? $input['accepter'] : null;
                $recordUpdate['accept_status'] = $input['accepter'] != -1 ? 1 : 0;
                $recordUpdate['accept_time'] = $input['accepter'] != -1 ? time() : null;
            } elseif (empty($input['accepter']) && isset($request->user->id)) {
                // 没有领取人则自己领取
                $recordUpdate['accepter'] = $request->user->id;
                $recordUpdate['accept_status'] = 1;
                $recordUpdate['accept_time'] = time();
            }

            $res = $record->update($recordUpdate);

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

            $details = '';
            // 开启事务
            DB::beginTransaction();
            $model = PostSubjectArticle::findOrFail($input['id']);
            if (!$model) {
                ReturnJson(FALSE, trans('lang.data_empty'));
            }
            $this->ValidateInstance($request);
            // 记录修改前的原始数据
            $originalAttributes = $model->getAttributes();
            $res = $model->update($input);
            // 获取修改后的数据
            $changedAttributes = $model->getChanges();
            if (!$res) {
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            $space = '    ';
            $changeData = PostSubjectArticle::getAttributesChange($originalAttributes, $changedAttributes);
            if ($changeData && count($changeData) > 0) {
                $string = '';
                foreach ($changeData as $key => $value) {
                    $string .= $space . '【' . $value['label'] . '】从【' . $value['before'] . '】修改成【' . $value['after'] . "】\n";
                }
                $details .= $string;
            }

            $postSubjectId = $model->id;

            // 最后宣传时间
            $lastPropagateTime = 0;
            // 已存在的数据
            $existLinkData = PostSubjectArticleLink::query()->select('id', 'link', 'created_at')->where(['post_subject_id' => $postSubjectId])->get()->toArray();
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
                $isDelete = PostSubjectArticleLink::query()->whereIn('id', $deleteIds)->delete();
                $isDelete = $isDelete > 0 ? true : false;
                if ($isDelete) {
                    $details = $space . '删除了' . $isDelete . '个链接' . "\n";
                }
                // $deleteRecord = PostSubjectArticleLink::query()->whereIn('id', $deletedIds)->update(['status' => 0]);
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
                    $postPlatformId = 0;
                    if ($postPlatformData) {
                        foreach ($postPlatformData as $postPlatformItem) {
                            if (strpos($urlItem, $postPlatformItem['keywords']) !== false) {
                                $postPlatformId = $postPlatformItem['id'];
                                break;
                            }
                        }
                    } else {
                        ReturnJson(false, '没有平台数据');
                        continue;
                    }
                    if (!isset($postPlatformId) || empty($postPlatformId)) {
                        ReturnJson(false, '【' . $urlItem . '】 没有对应平台');
                        continue;
                    }

                    $inputChild = [];
                    $inputChild['post_subject_id'] = $model->id;
                    $inputChild['link'] = $urlItem;
                    $inputChild['post_platform_id'] = $postPlatformId;
                    $inputChild['status'] = 1;
                    $inputChild['sort'] = 100;
                    $postSubjectLinkModel = new PostSubjectArticleLink();
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

                    $details = $space . '新增了' . $insertCount . '个链接' . "\n";
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

            // 更新修改状态，在链接有变动时
            if ($isInsert || $isDelete) {
                $recordUpdate['change_status'] = 0;
            }

            // 更新最后宣传时间
            if ($isInsert || !empty($lastPropagateTime)) {
                $recordUpdate['propagate_status'] = 1;
                $recordUpdate['last_propagate_time'] = $isInsert ? time() : $lastPropagateTime;
            } elseif (empty($lastPropagateTime)) {
                $recordUpdate['propagate_status'] = 0;
                $recordUpdate['last_propagate_time'] = null;
            }

            // 更新领取人
            if (!empty($input['accepter'])) {
                $recordUpdate['accept_time'] = time();
                $recordUpdate['accepter'] = $input['accepter'] != -1 ? $input['accepter'] : null;
                $recordUpdate['accept_status'] = $input['accepter'] != -1 ? 1 : 0;
            } elseif (empty($input['accepter'])) {
                // 没有领取人则自己领取
                $recordUpdate['accept_time'] = time();
                $recordUpdate['accepter'] = $request->user->id;
                $recordUpdate['accept_status'] = 1;
            }
            if (count($recordUpdate) > 0) {
                $res = $model->update($recordUpdate);
            }
            DB::commit();

            // 添加日志
            if (!empty($details)) {
                $log = new PostSubjectLog();
                $logData['type'] = PostSubjectLog::POST_SUBJECT_CURD;
                $logData['post_subject_id'] = $model->id;
                $logData['details'] = date('Y-m-d H:i:s', time()) . ' 操作人【' . $request->user->nickname . '】-' . "\n" . $details;
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
        $id = $input['id'] ?? '';
        $name = $input['name'] ?? '';
        if (!empty($name)) {

            $query = PostSubjectArticle::query()->where('name', trim($name));
            if (!empty($id)) {
                $query->where('id', '<>', $id);
            }
            $data  = $query->value('id');

            ReturnJson(true, trans('lang.request_success'), $data ?? 0);
        }
        ReturnJson(true, trans('lang.request_success'), 0);
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

            $record['accepter_name'] = User::query()->where('id', $record['accepter'])->value('nickname') ?? '';
            $record['last_propagate_time_format'] = !empty($record['last_propagate_time']) ? date('Y-m-d H:i:s', $record['last_propagate_time']) : '';
            $record['accept_time_format'] = !empty($record['accept_time']) ? date('Y-m-d H:i:s', $record['accept_time']) : '';

            $urlData = PostSubjectArticleLink::query()->where(['post_subject_id' => $record['id']])->get()->toArray();
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

            $model = PostSubjectArticle::from('post_subject_article as ps');
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

                $subjectOwnId = NULL;
                if (isset($request->subjectOwn) && $request->subjectOwn == 1) {
                    $subjectOwnId = -1;
                } elseif (isset($request->subjectOwn) && $request->subjectOwn == 2) {
                    $subjectOwnId = $request->user->id;
                }
                $model = $this->ModelInstance()->getFiltersQuery($model, $searchJson, $subjectOwnId);
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

                DB::beginTransaction();
                $rs = PostSubjectArticle::whereIn('id', $idsData)->delete();
                if (!$rs) {
                    DB::rollBack();
                    ReturnJson(FALSE, trans('lang.delete_error'));
                }
                //删除子项
                PostSubjectArticleLink::whereIn('post_subject_id', $idsData)->delete();

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


        $model = PostSubjectArticle::from('post_subject_article as ps');
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
            $subjectOwnId = NULL;
            if (isset($request->subjectOwn) && $request->subjectOwn == 1) {
                $subjectOwnId = -1;
            } elseif (isset($request->subjectOwn) && $request->subjectOwn == 2) {
                $subjectOwnId = $request->user->id;
            }
            $model = $this->ModelInstance()->getFiltersQuery($model, $searchJson, $subjectOwnId);
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
                'accept_time' => $accepter != -1 ? time() : null,
                'accept_status' => $accepter != -1 ? 1 : 0,
                'updated_by' => $request->user->id,
            ];
            PostSubjectArticle::query()->whereIn("id", $idsData)->update($updateData);
            // 添加日志
            // $logData = [];
            // foreach ($idsData as $key => $id) {
            //     $logDataChild = [];
            //     $logDataChild['type'] = PostSubjectLog::POST_SUBJECT_ACCEPT;
            //     $logDataChild['post_subject_id'] = $id;
            //     if ($isOwn) {
            //         $logDataChild['details'] = date('Y-m-d H:i:s', time()) . ' 操作人【' . $accepterName . '】领取了课题';
            //     } else {
            //         $logDataChild['details'] = date('Y-m-d H:i:s', time()) . ' 操作人【' . $request->user->nickname . '】将课题分配给【' . $accepterName . '】';
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
                $details[] = date('Y-m-d H:i:s', time()) . ' 操作人【' . $accepterName . '】领取了' . $acceptCount . '个课题';
            } else {
                $details[] = date('Y-m-d H:i:s', time()) . ' 操作人【' . $request->user->nickname . '】将' . $acceptCount . '个课题分配给【' . $accepterName . '】';
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
     * 导出日志(课题含帖子)
     */
    public function exportSubjectLink(Request $request)
    {

        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", '-1'); // 不限制内存

        $input = $request->all();
        $ids = $input['ids'] ?? '';

        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作

        $model = PostSubjectArticle::from('post_subject_article as ps');
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

            $subjectOwnId = NULL;
            if (isset($request->subjectOwn) && $request->subjectOwn == 1) {
                $subjectOwnId = -1;
            } elseif (isset($request->subjectOwn) && $request->subjectOwn == 2) {
                $subjectOwnId = $request->user->id;
            }
            $model = $this->ModelInstance()->getFiltersQuery($model, $searchJson, $subjectOwnId);
        }

        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            // //查询出涉及的id
            // $idsData = $model->select('id')->pluck('id')->toArray();
            $subjectData = $model->select(['id', 'name',  'accepter', 'keywords',])->get()->toArray();
            if (!(count($subjectData) > 0)) {
                ReturnJson(true, trans('lang.data_empty'));
            }
        }

        // 查询帖子
        $subjectIds = array_column($subjectData, 'id');
        $subjectLinkData = PostSubjectArticleLink::query()->select([
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
        // ReturnJson(TRUE, trans('lang.request_success'), $subjectGroup);

        // 领取人列表
        $accepterIds = array_column($subjectData, 'accepter');
        $accepterList = User::query()->whereIn('id', $accepterIds)->pluck('nickname', 'id')->toArray();


        $domain = env('APP_DOMAIN');
        $site = request()->header("Site");
        $date = date('Ymd', time());

        // 循环课题，输出excel
        $excelHeader = [
            '课题',
            '版本',
            '快速搜索链接',
            '发贴链接',
            '关键词',
            '是否有数据',
        ];
        $details = [];
        $subjectSuccess = 0;
        $subjectLinkSuccess = 0;
        $subjectFail = 0;
        $subjectLinkFail = 0;
        $firstSheetName = '';
        $space = '    ';

        // $writer->close();
        // 创建 Spreadsheet 对象
        $spreadsheet = new Spreadsheet();

        // 设置文件名
        $date = date('Ymd');
        $filename = 'export-posts-' . count($subjectData) . '-' . $date . '.xlsx';

        // 遍历每个领取人的数据
        $firstSheetName = '';
        foreach ($subjectGroup as $groupAccepterId => $subjectGroupItem) {
            $sheetName = $accepterList[$groupAccepterId] ?? '';

            if (empty($sheetName)) {
                // 处理找不到领取人的情况
                $subjectFail += count($subjectGroupItem);
                $details[] = $groupAccepterId == 0 ? $space . '【错误: 领取人为公客】' : $space . '【错误: 领取人ID' . $groupAccepterId . '不存在】';
                foreach ($subjectGroupItem as $subject) {
                    $details[] = $space . $space . '--【编号' . $subject['id'] . '】' . $subject['name'];
                    if (count($details) > 50) {
                        $details[] = $space . $space . '-- ...';
                        break;
                    }
                }
                continue;
            }

            // 确定工作簿是否为第一个工作簿
            if (empty($firstSheetName)) {
                $firstSheetName = $sheetName;
            }

            // 添加新工作表
            if ($firstSheetName == $sheetName) {
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle($sheetName);
            } else {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($sheetName);
            }

            $sheet->getColumnDimension('A')->setWidth(55);  // 设置 A 列宽度
            $sheet->getColumnDimension('B')->setWidth(20);  // 设置 B 列宽度
            $sheet->getColumnDimension('C')->setWidth(80);  // 设置 C 列宽度
            $sheet->getColumnDimension('D')->setWidth(80);  // 设置 D 列宽度
            $sheet->getColumnDimension('E')->setWidth(20);  // 设置 E 列宽度
            $sheet->getColumnDimension('F')->setWidth(20);  // 设置 F 列宽度

            // 添加标题行
            $sheet->fromArray($excelHeader, null, 'A1');

            // 填充数据
            $rowIndex = 1;
            foreach ($subjectGroupItem as $subject) {

                // 发帖链接
                if (isset($subjectLinkGroup[$subject['id']]) && is_array($subjectLinkGroup[$subject['id']]) && count($subjectLinkGroup[$subject['id']]) > 0) {
                    $subjectSuccess++;
                    foreach ($subjectLinkGroup[$subject['id']] as $linkIndex => $linkValue) {
                        $linkValue = !empty($linkValue) ? $linkValue : "";

                        if ($linkIndex != 0) {
                            // 名称
                            $sheet->setCellValue([0 + 1, $rowIndex + 1], '');
                            // 版本
                            $sheet->setCellValue([1 + 1, $rowIndex + 1], '');
                            // 搜索链接
                            $sheet->setCellValue([2 + 1, $rowIndex + 1], '');
                        } else {
                            $url = $domain . '/#/' . $site . '/products/fastList?type=id&keyword=' . $subject['product_id'];
                            // 名称
                            $sheet->setCellValue([0 + 1, $rowIndex + 1], $subject['name']);
                            // 版本
                            $sheet->setCellValue([1 + 1, $rowIndex + 1], $subject['version']);
                            // 搜索链接
                            $sheet->setCellValue([2 + 1, $rowIndex + 1], $url);
                            $sheet->getCell([2 + 1, $rowIndex + 1])->getHyperlink()->setUrl($url);
                            $sheet->getStyle([2 + 1, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB('0000FF');

                            $sheet->setCellValue([4 + 1, $rowIndex + 1], $subject['keywords']); // 关键词
                            // $sheet->setCellValue([5 + 1, $rowIndex + 1], !empty($subject['has_cagr']) ? '是' : '否'); // 是否有数据
                        }
                        // 发帖链接
                        $sheet->setCellValue([3 + 1, $rowIndex + 1], $linkValue);
                        $sheet->getCell([3 + 1, $rowIndex + 1])->getHyperlink()->setUrl($linkValue);
                        $sheet->getStyle([3 + 1, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB('0000FF');

                        $subjectLinkSuccess++;
                        $rowIndex++;
                    }
                } elseif (!isset($subjectLinkGroup[$subject['id']]) && $subject) {
                    // 没有宣传链接也要把课题写入文件
                    $subjectSuccess++;
                    $url = $domain . '/#/' . $site . '/products/fastList?type=id&keyword=' . $subject['product_id'];
                    // 名称
                    $sheet->setCellValue([0 + 1, $rowIndex + 1], $subject['name']);
                    // 版本
                    $sheet->setCellValue([1 + 1, $rowIndex + 1], $subject['version']);
                    // 关键词
                    $sheet->setCellValue([4 + 1, $rowIndex + 1], $subject['keywords']);
                    // 是否有数据
                    // $sheet->setCellValue([5 + 1, $rowIndex + 1], !empty($subject['has_cagr']) ? '是' : '否');
                    // 搜索链接
                    $sheet->setCellValue([2 + 1, $rowIndex + 1], $url);
                    $sheet->getCell([2 + 1, $rowIndex + 1])->getHyperlink()->setUrl($url);
                    $sheet->getStyle([2 + 1, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB('0000FF');
                    $rowIndex++;
                }
            }
        }

        // 设置 HTTP 头部并输出文件
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $exportCount = count($subjectData);
        if ($exportCount) {
            $logData = [];
            $logData['type'] = PostSubjectLog::POST_SUBJECT_LINK_EXPORT;
            // $logData['post_subject_id'] = ;
            $logData['success_count'] = $subjectSuccess;
            $logData['ingore_count'] = $subjectFail;
            $logData['details'] = '';
            $logData['details'] .= date('Y-m-d H:i:s', time()) . ' 操作人【' . $request->user->nickname . '】-' . "\n";
            $logData['details'] .= $space . '成功导出' . $subjectSuccess . '个课题, ' . $subjectLinkSuccess . '个链接, ' . '有' . $subjectFail . '个课题导出失败' . "\n";
            $logData['details'] .= implode("\n", $details);
            PostSubjectLog::create($logData);
        }

        exit;
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
