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
use Modules\Site\Http\Models\PostSubjectArticle;
use Modules\Site\Http\Models\PostSubjectArticleLink;
use Modules\Site\Http\Models\PostSubjectLink;
use Modules\Site\Http\Models\PostSubjectLog;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Requests\ProductsCategoryRequest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Foolz\SphinxQL\SphinxQL;
use Modules\Site\Http\Models\PersonalSetting;
use Modules\Site\Http\Models\PostSubjectFilter;
use Modules\Site\Services\SphinxService;

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
                'type',
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
                'keywords',
                'has_cagr',
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
                    $record[$key]['type_name'] = PostSubject::getTypeList()[$record[$key]['type']] ?? '';
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
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_CONTAIN, PostSubject::CONDITION_NOT_CONTAIN);
        $temp_filter = $this->getAdvancedFiltersItem('name', '报告名称', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        // 关键词
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_CONTAIN, PostSubject::CONDITION_NOT_CONTAIN);
        $temp_filter = $this->getAdvancedFiltersItem('keywords', '关键词', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        // 类型
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = PostSubject::getTypeDropList();
        $temp_filter = $this->getAdvancedFiltersItem('type', '课题类型', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($showData, $temp_filter);

        // 行业
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_IN, PostSubject::CONDITION_NOT_IN);
        $options = (new ProductsCategory())->GetList(['id as value', 'name as label', 'id', 'pid'], true, 'pid', ['status' => 1]);
        $temp_filter = $this->getAdvancedFiltersItem('product_category_id', '行业', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, true, $options);
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
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Accept_State', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('accept_status', '领取状态', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
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


        // 是否有数据
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Has_Cagr', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('has_cagr', '是否有数据', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($showData, $temp_filter);


        // id
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $temp_filter = $this->getAdvancedFiltersItem('id', '课题ID', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);

        // product_id
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $temp_filter = $this->getAdvancedFiltersItem('product_id', '报告ID', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($showData, $temp_filter);



        /**
         * 隐藏条件
         */


        // 分析师
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_CONTAIN, PostSubject::CONDITION_NOT_CONTAIN);
        $temp_filter = $this->getAdvancedFiltersItem('analyst', '分析师', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($hiddenData, $temp_filter);

        // 版本
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_CONTAIN, PostSubject::CONDITION_NOT_CONTAIN);
        $temp_filter = $this->getAdvancedFiltersItem('version', '版本', PostSubject::ADVANCED_FILTERS_TYPE_TEXT, $condition);
        array_push($hiddenData, $temp_filter);


        // 修改状态
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Change_State', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('change_status', '修改状态', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($hiddenData, $temp_filter);

        // 是否有关键词(中)
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Has_Cagr', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('keywords_cn', '是否有关键词(中)', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($hiddenData, $temp_filter);

        // 是否有关键词(英)
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Has_Cagr', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('keywords_en', '是否有关键词(英)', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($hiddenData, $temp_filter);

        // 是否有关键词(日)
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Has_Cagr', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('keywords_jp', '是否有关键词(日)', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($hiddenData, $temp_filter);

        // 是否有关键词(韩)
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Has_Cagr', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('keywords_kr', '是否有关键词(韩)', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($hiddenData, $temp_filter);

        // 是否有关键词(德)
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_EQUAL, PostSubject::CONDITION_NOT_EQUAL);
        $options = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Has_Cagr', 'status' => 1], ['sort' => 'ASC']);
        $temp_filter = $this->getAdvancedFiltersItem('keywords_de', '是否有关键词(德)', PostSubject::ADVANCED_FILTERS_TYPE_DROPDOWNLIST, $condition, false, $options);
        array_push($hiddenData, $temp_filter);



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

        // 领取时间
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_TIME_BETWEEN, PostSubject::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('accept_time', '领取时间', PostSubject::ADVANCED_FILTERS_TYPE_TIME, $condition);
        array_push($hiddenData, $temp_filter);

        // 最后宣传时间
        $condition = PostSubject::getFiltersCondition(PostSubject::CONDITION_TIME_BETWEEN, PostSubject::CONDITION_TIME_NOT_BETWEEN);
        $temp_filter = $this->getAdvancedFiltersItem('last_propagate_time', '最后宣传时间', PostSubject::ADVANCED_FILTERS_TYPE_TIME, $condition);
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
            $data['propagate_status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Post_Subject_Has_Cagr', 'status' => 1], ['sort' => 'ASC']);


            $data['type'] = PostSubject::getTypeDropList();

            // 领取人/发帖用户
            $data['accepter_list'] = (new TemplateController())->getSitePostUser();
            if (count($data['accepter_list']) > 0) {
                array_unshift($data['accepter_list'], ['label' => '公客', 'value' => '-1']);
            }

            $exportSettingKey = 'export_subject_extra_line';
            $user_id = $request->user->id;
            $data['export_setting'] = [];
            $data['export_setting'][$exportSettingKey] = [
                'key' => $exportSettingKey,
                'value' => 0,
            ];
            $exportSetting = PersonalSetting::query()->select('value')->where(['user_id' => $user_id, 'key' => $exportSettingKey])->value('value');
            if (!$exportSetting) {
                $exportSetting = PersonalSetting::query()->select('value')->where(['key' => $exportSettingKey])->value('value');
            }
            if ($exportSetting) {
                $data['export_setting'][$exportSettingKey]['value'] = $exportSetting;
            }
            $data['export_setting'] = array_values($data['export_setting']);

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
            if ($input['type'] == PostSubject::TYPE_POST_SUBJECT) {

                if (!empty($input['product_id'])) {
                    $cagr = Products::query()->where(['id' => $input['product_id']])->value('cagr');

                    if ($cagr && !empty($cagr)) {
                        $input['has_cagr'] = 1;
                    } else {
                        $input['has_cagr'] = 0;
                    }
                } else {
                    $input['has_cagr'] = 0;
                }
            } elseif ($input['type'] == PostSubject::TYPE_POST_ARTICLE) {
                $input['has_cagr'] = 0;
                $input['product_id'] = 0;
                $input['product_category_id'] = 0;
                $input['analyst'] = null;
                $input['version'] = null;
            } else {
                ReturnJson(false, trans('lang.add_error'), '未知课题类型');
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

                $urlData = array_map(function ($item) {
                    return trim($item['link'] ?? '');
                }, $urlData);

                $postPlatformData = PostPlatform::query()->select(['id', 'name', 'keywords'])->where('status', 1)->get()->toArray();

                $existLinkBySubject = [];
                foreach ($urlData as $key => $urlItem) {

                    // 没填跳过
                    if (empty(trim($urlItem ?? ''))) {
                        continue;
                    }

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

                    $removeProtocolLink = trim(trim(trim(trim($urlItem), 'https://'), 'http://'), '/');
                    if (in_array($removeProtocolLink, $existLinkBySubject)) {
                        continue;
                    }
                    $existLinkBySubject[] = $removeProtocolLink;

                    $inputChild = [];
                    $inputChild['post_subject_id'] = $record->id;
                    $inputChild['link'] = $urlItem;
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


            if ($input['type'] == PostSubject::TYPE_POST_SUBJECT) {

                if (!empty($input['product_id'])) {
                    $cagr = Products::query()->where(['id' => $input['product_id']])->value('cagr');

                    if ($cagr && !empty($cagr)) {
                        $input['has_cagr'] = 1;
                    } else {
                        $input['has_cagr'] = 0;
                    }
                } else {
                    $input['has_cagr'] = 0;
                }
            } elseif ($input['type'] == PostSubject::TYPE_POST_ARTICLE) {
                if (empty(trim($input['keywords']))) {
                    ReturnJson(false, trans('lang.param_empty'), '观点文关键词不能为空');
                }

                $input['has_cagr'] = 0;
                $input['product_id'] = 0;
                $input['product_category_id'] = 0;
                $input['analyst'] = null;
                $input['version'] = null;
            } else {
                ReturnJson(false, trans('lang.param_empty'), '未知课题类型');
            }

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
            $changedAttributes = $model->getChanges();
            if (!$res) {
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            $space = '    ';
            $changeData = PostSubject::getAttributesChange($originalAttributes, $changedAttributes);
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
                    $details = $space . '删除了' . $isDelete . '个链接' . "\n";
                }
                // $deleteRecord = PostSubjectLink::query()->whereIn('id', $deletedIds)->update(['status' => 0]);
            }

            if ($insertUrl && count($insertUrl) > 0) {
                // 平台列表
                $postPlatformData = PostPlatform::query()->select(['id', 'name', 'keywords'])->where('status', 1)->get()->toArray();
                // 新增子项
                $insertCount = 0;
                $existLinkBySubject = [];
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

                    $removeProtocolLink = trim(trim(trim(trim($urlItem), 'https://'), 'http://'), '/');
                    if (in_array($removeProtocolLink, $existLinkBySubject)) {
                        continue;
                    }
                    $existLinkBySubject[] = $removeProtocolLink;

                    $inputChild = [];
                    $inputChild['post_subject_id'] = $model->id;
                    $inputChild['link'] = $urlItem;
                    $inputChild['post_platform_id'] = $postPlatformId;
                    $inputChild['status'] = 1;
                    $inputChild['sort'] = 100;
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
        $product_id = $input['product_id'] ?? '';
        $product_name = $input['name'] ?? '';
        $type = $input['type'] ?? null;
        if (empty($type)) {
            ReturnJson(false, trans('lang.param_empty'), '缺少课题类型');
        }
        $result = [];
        if ($type == PostSubject::TYPE_POST_SUBJECT) {

            $productQueryWhere = [];

            $isExist = null;
            $isExistQuery = PostSubject::query()->where('type', $type);
            if (!empty($product_id)) {
                $productQueryWhere = ['id' => $product_id]; // 给后面查报告信息
                $isExistQuery->where('product_id', $product_id);
                if ($id) {
                    $isExistQuery->where('id', '<>', $id);
                }

                $isExist = $isExistQuery->first();
            } elseif (!empty($product_name)) {
                $productQueryWhere = ['name' => trim($product_name)]; // 给后面查报告信息
                $isExistQuery->where('name', trim($product_name));
                if ($id) {
                    $isExistQuery->where('id', '<>', $id);
                }
                $isExist = $isExistQuery->first();
            } else {
                ReturnJson(false, trans('lang.param_empty'), '缺少参数-报告id或者课题名称');
            }


            $productQuery = Products::query()->select([
                'id as product_id',
                'name',
                'category_id as product_category_id',
                'author as analyst',
                'price as version',
                'keywords',
                'cagr',
            ]);

            if ($isExist) {
                $result = [
                    'data' => [
                        'id' => $isExist->id,
                    ],
                    'redirect' => true,
                    'msg' => '已存在相关的课题,不可用此课题名称或报告id',
                ];
            } else {
                // 不存在冲突课题,查询报告信息，返回给前端填充
                $productData = $productQuery->where($productQueryWhere)->first()?->makeHidden((new Products())->getAppends())->toArray() ?? null;
                if ($productData) {

                    $productData['version'] = floatval($productData['version']);
                    $productData['has_cagr'] = !empty($productData['cagr']) ? 1 : 0;
                    $result = [
                        'data' => $productData,
                        'redirect' => false,
                        'msg' => '可以新增修改',
                    ];
                } else {

                    $result = [
                        'data' => [],
                        'redirect' => false,
                        'msg' => '报告不存在',
                    ];
                }
            }

            ReturnJson(true, trans('lang.request_success'), $result);
        } elseif ($type == PostSubject::TYPE_POST_ARTICLE) {

            // 查询 类型为观点，课题名称+领取人的组合是否存在
            $isExist = null;
            $isExistQuery = PostSubject::query()->where('type', $type);
            if (!empty($product_name)) {
                $isExistQuery->where('name', trim($product_name));
                $accepter = $request->user->id;
                $isExistQuery->where('accepter', $accepter);
                if ($id) {
                    $isExistQuery->where('id', '<>', $id);
                }
                $isExist = $isExistQuery->first();
            } else {
                ReturnJson(false, trans('lang.param_empty'), '缺少参数-课题名称');
            }
            if ($isExist) {
                $result = [
                    'data' => [
                        'id' => $isExist->id,
                    ],
                    'redirect' => true,
                    'msg' => '已存在相关的观点文章,不可用此名称',
                ];
            } else {
                $result = [
                    'data' => [],
                    'redirect' => false,
                    'msg' => '观点可以新增修改',
                ];
            }
            ReturnJson(true, trans('lang.request_success'), $result);
        } else {
            ReturnJson(false, trans('lang.param_empty'), '未知课题类型');
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
                $rs = PostSubject::whereIn('id', $idsData)->delete();
                if (!$rs) {
                    DB::rollBack();
                    ReturnJson(FALSE, trans('lang.delete_error'));
                }
                //删除子项
                PostSubjectLink::whereIn('post_subject_id', $idsData)->delete();

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
        $isFilter = $input['is_filter'] ?? 0;

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
            $isFilter = PostSubjectFilter::POST_SUBJECT_READ;
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
            $postSubjectData = $model->select(['id', 'name', 'keywords'])->get()->toArray();
            $idsData = array_column($postSubjectData, 'id');

            if ($isFilter == PostSubjectFilter::POST_SUBJECT_JOIN) {
                // 加入过滤列表
                $insertFilterData = [];
                // $insertFilterColumn = ['user_id', 'keywords', 'created_by', 'created_at', 'updated_by', 'updated_at',];
                $filterUserId = $request->user->id;
                $time = time();
                $tempKeywordsArray = [];
                foreach ($idsData as $key => $subject_id) {
                    $tempKeywords = $postSubjectData[$subject_id]['keywords'] ?? '';
                    if (!empty($tempKeywords)) {
                        $tempKeywordsArray[] = $tempKeywords;
                    }
                }

                $filterKeywordsData = PostSubjectFilter::query()
                    ->select(['keywords'])
                    ->where('user_id', $filterUserId)
                    ->whereIn('keywords', $tempKeywordsArray)
                    ->pluck('keywords')?->toArray() ?? [];

                foreach ($idsData as $key => $subject_id) {
                    $tempKeywords = $postSubjectData[$subject_id]['keywords'] ?? '';
                    if(empty($tempKeywords)){
                        continue;
                    }
                    if (in_array($tempKeywords, $filterKeywordsData)) {
                        continue;
                    }
                    $insertFilterData[] = [
                        'user_id' => $filterUserId,
                        'keywords' => $tempKeywords,
                        'created_by' => $filterUserId,
                        'created_at' => $time,
                        'updated_by' => $filterUserId,
                        'updated_at' => $time,
                    ];
                }
                if (!empty($insertFilterData) && count($insertFilterData)>0) {
                    PostSubjectFilter::insert($insertFilterData);
                }
            } elseif ($isFilter == PostSubjectFilter::POST_SUBJECT_READ) {
                // 领取的课题id根据过滤列表过滤
                $filterKeywordsData = PostSubjectFilter::query()->select(['keywords'])->where('user_id' , $accepter)->pluck('keywords')?->toArray() ?? [];
                $newIdsData = $idsData;
                foreach ($idsData as $key => $subject_id) {
                    $tempKeywords = $postSubjectData[$subject_id]['keywords'] ?? '';
                    if (!empty($tempKeywords) && in_array($tempKeywords, $filterKeywordsData)) {
                        // unset();
                        unset($newIdsData[$key]);
                    }
                }
                $idsData = array_values($newIdsData);
            }


            // 领取操作
            $updateData = [
                'accepter' => $accepter != -1 ? $accepter : null,
                'accept_time' => $accepter != -1 ? time() : null,
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
    // 移入公客
    public function moveInCommon(Request $request)
    {
        $request->replace([
            'accepter' => -1,
            'is_filter' => PostSubjectFilter::POST_SUBJECT_JOIN
        ]);
        return $this->accept($request);
    }

    /**
     * 导出课题
     */
    public function exportSubject(Request $request)
    {

        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", '-1'); // 不限制内存
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
            $model = $model->whereIn('ps.id', $ids);
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
            $subjectData = $model->select([
                'ps.id',
                'ps.name',
                'ps.product_id',
                'ps.version',
                'ps.accepter',
                'ps.keywords',
                'ps.has_cagr',
                'pc.name as category_name',
                'p.keywords_cn',
                'p.keywords_en',
                'p.keywords_jp',
                'p.keywords_kr',
                'p.keywords_de',
            ])
                // ->from($model->getTable() . ' as ps')
                ->leftJoin((new Products)->getTable() . ' as p', function ($join) {
                    $join->on('p.id', '=', 'ps.product_id');
                })
                ->leftJoin((new ProductsCategory())->getTable() . ' as pc', function ($join) {
                    $join->on('pc.id', '=', 'ps.product_category_id');
                })
                ->get()->toArray();
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
            '关键词',
            '是否有数据',
            '关键词(中)',
            '关键词(英)',
            '关键词(日)',
            '关键词(韩)',
            '关键词(德)',
            '行业',
        ];

        // 创建 Spreadsheet 对象
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置 Sheet 名称
        $accepter = $subjectData[0]['accepter'] ?? 0;
        if (!empty($accepter)) {
            $sheetName = User::query()->where('id', $accepter)->value('nickname') ?? 'Sheet1';
        } else {
            $sheetName = $request->user->nickname ?? 'Sheet1';
        }
        $sheet->setTitle($sheetName);

        // 设置标题行
        $sheet->fromArray([$excelHeader], null, 'A1');

        // 填充数据
        $rowIndex = 1;
        $sheet->getColumnDimension('A')->setWidth(40);  // 设置 A 列宽度
        $sheet->getColumnDimension('B')->setWidth(15);  // 设置 B 列宽度
        $sheet->getColumnDimension('C')->setWidth(60);  // 设置 C 列宽度
        $sheet->getColumnDimension('D')->setWidth(60);  // 设置 D 列宽度
        $sheet->getColumnDimension('E')->setWidth(15);  // 设置 E 列宽度
        $sheet->getColumnDimension('F')->setWidth(15);  // 设置 F 列宽度
        $sheet->getColumnDimension('G')->setWidth(15);  // 设置 G 列宽度
        $sheet->getColumnDimension('H')->setWidth(15);  // 设置 H 列宽度
        $sheet->getColumnDimension('I')->setWidth(15);  // 设置 I 列宽度
        $sheet->getColumnDimension('J')->setWidth(15);  // 设置 J 列宽度
        $sheet->getColumnDimension('K')->setWidth(15);  // 设置 K 列宽度
        $sheet->getColumnDimension('L')->setWidth(15);  // 设置 L 列宽度



        $key = 'export_subject_extra_line';
        $user_id = $request->user->id;
        $extraLine = PersonalSetting::query()->select('value')->where(['user_id' => $user_id, 'key' => $key])->value('value');
        if (!$extraLine) {
            $extraLine = PersonalSetting::query()->select('value')->where(['key' => $key, 'user_id' => 0])->value('value');
        }

        foreach ($subjectData as $subject) {
            if (!empty($subject['product_id'])) {
                $url = $domain . '/#/' . $site . '/products/fastList?type=id&keyword=' . $subject['product_id'];
            } else {
                $url = '';
            }

            $sheet->setCellValue([0 + 1, $rowIndex + 1], $subject['name']);
            $sheet->setCellValue([1 + 1, $rowIndex + 1], $subject['version']);

            if (!empty($url)) {
                // 设置超链接
                $sheet->setCellValue([2 + 1, $rowIndex + 1], $url);
                $sheet->getCell([2 + 1, $rowIndex + 1])->getHyperlink()->setUrl($url);
                $sheet->getStyle([2 + 1, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB('0000FF');
            }

            $sheet->setCellValue([3 + 1, $rowIndex + 1], ''); // 额外空白列

            $sheet->setCellValue([4 + 1, $rowIndex + 1], $subject['keywords']); // 关键词
            $sheet->setCellValue([5 + 1, $rowIndex + 1], !empty($subject['has_cagr']) ? '是' : '否'); // 是否有数据

            $sheet->setCellValue([6 + 1, $rowIndex + 1], !empty($subject['keywords_cn']) ? '有' : ''); // 关键词(中)
            $sheet->setCellValue([7 + 1, $rowIndex + 1], !empty($subject['keywords_en']) ? '有' : ''); // 关键词(英)
            $sheet->setCellValue([8 + 1, $rowIndex + 1], !empty($subject['keywords_jp']) ? '有' : ''); // 关键词(日)
            $sheet->setCellValue([9 + 1, $rowIndex + 1], !empty($subject['keywords_kr']) ? '有' : ''); // 关键词(韩)
            $sheet->setCellValue([10 + 1, $rowIndex + 1], !empty($subject['keywords_de']) ? '有' : ''); // 关键词(德)

            $sheet->setCellValue([11 + 1, $rowIndex + 1], $subject['category_name'] ?? ''); // 行业

            $rowIndex++;
            $rowIndex += empty($extraLine) ? 0 : $extraLine;
            $details[] = '【课题编号' . $subject['id'] . '】' . $subject['name'];
        }

        // 设置 HTTP 头部并导出文件
        $date = date('Ymd');
        $filename = 'export-topic-' . count($subjectData) . '-' . $date . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        // exit;
        $exportCount = count($subjectData);

        if ($exportCount) {
            $logData = [];
            $logData['type'] = PostSubjectLog::POST_SUBJECT_EXPORT;
            $logData['success_count'] = $exportCount;
            $logData['ingore_count'] = 0;
            // $logData['post_subject_id'] = ;
            $logData['details'] = date('Y-m-d H:i:s', time()) . ' 操作人【' . $request->user->nickname . '】导出了' . $exportCount . '个课题' . "\n" . (implode("\n", $details));
            PostSubjectLog::create($logData);
        }
        exit;
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

        $model = PostSubject::from('post_subject as ps');
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty') . ':ids');
            }
            $model = $model->whereIn('ps.id', $ids);
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
            $subjectData = $model->select([
                'ps.id',
                'ps.name',
                'ps.product_id',
                'ps.version',
                'ps.accepter',
                'ps.keywords',
                'ps.has_cagr',
                'pc.name as category_name',
                'p.keywords_cn',
                'p.keywords_en',
                'p.keywords_jp',
                'p.keywords_kr',
                'p.keywords_de',
            ])
                // ->from($model->getTable() . ' as ps')
                ->leftJoin((new Products)->getTable() . ' as p', function ($join) {
                    $join->on('p.id', '=', 'ps.product_id');
                })
                ->leftJoin((new ProductsCategory())->getTable() . ' as pc', function ($join) {
                    $join->on('pc.id', '=', 'ps.product_category_id');
                })
                ->get()->toArray();
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
            '关键词(中)',
            '关键词(英)',
            '关键词(日)',
            '关键词(韩)',
            '关键词(德)',
            '行业',
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

            $sheet->getColumnDimension('A')->setWidth(40);  // 设置 A 列宽度
            $sheet->getColumnDimension('B')->setWidth(15);  // 设置 B 列宽度
            $sheet->getColumnDimension('C')->setWidth(60);  // 设置 C 列宽度
            $sheet->getColumnDimension('D')->setWidth(60);  // 设置 D 列宽度
            $sheet->getColumnDimension('E')->setWidth(15);  // 设置 E 列宽度
            $sheet->getColumnDimension('F')->setWidth(15);  // 设置 F 列宽度
            $sheet->getColumnDimension('G')->setWidth(15);  // 设置 G 列宽度
            $sheet->getColumnDimension('H')->setWidth(15);  // 设置 H 列宽度
            $sheet->getColumnDimension('I')->setWidth(15);  // 设置 I 列宽度
            $sheet->getColumnDimension('J')->setWidth(15);  // 设置 J 列宽度
            $sheet->getColumnDimension('K')->setWidth(15);  // 设置 K 列宽度
            $sheet->getColumnDimension('L')->setWidth(15);  // 设置 L 列宽度

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
                            if (!empty($subject['product_id'])) {
                                $url = $domain . '/#/' . $site . '/products/fastList?type=id&keyword=' . $subject['product_id'];
                            } else {
                                $url = '';
                            }
                            // 名称
                            $sheet->setCellValue([0 + 1, $rowIndex + 1], $subject['name']);
                            // 版本
                            $sheet->setCellValue([1 + 1, $rowIndex + 1], $subject['version']);
                            if (!empty($url)) {
                                // 搜索链接
                                $sheet->setCellValue([2 + 1, $rowIndex + 1], $url);
                                $sheet->getCell([2 + 1, $rowIndex + 1])->getHyperlink()->setUrl($url);
                                $sheet->getStyle([2 + 1, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB('0000FF');
                            }

                            $sheet->setCellValue([4 + 1, $rowIndex + 1], $subject['keywords']); // 关键词
                            $sheet->setCellValue([5 + 1, $rowIndex + 1], !empty($subject['has_cagr']) ? '是' : '否'); // 是否有数据

                            $sheet->setCellValue([6 + 1, $rowIndex + 1], !empty($subject['keywords_cn']) ? '有' : ''); // 关键词(中)
                            $sheet->setCellValue([7 + 1, $rowIndex + 1], !empty($subject['keywords_en']) ? '有' : ''); // 关键词(英)
                            $sheet->setCellValue([8 + 1, $rowIndex + 1], !empty($subject['keywords_jp']) ? '有' : ''); // 关键词(日)
                            $sheet->setCellValue([9 + 1, $rowIndex + 1], !empty($subject['keywords_kr']) ? '有' : ''); // 关键词(韩)
                            $sheet->setCellValue([10 + 1, $rowIndex + 1], !empty($subject['keywords_de']) ? '有' : ''); // 关键词(德)

                            $sheet->setCellValue([11 + 1, $rowIndex + 1], $subject['category_name'] ?? ''); // 行业
                        }
                        if (!empty($linkValue)) {
                            // 发帖链接
                            $sheet->setCellValue([3 + 1, $rowIndex + 1], $linkValue);
                            $sheet->getCell([3 + 1, $rowIndex + 1])->getHyperlink()->setUrl($linkValue);
                            $sheet->getStyle([3 + 1, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB('0000FF');
                        }

                        $subjectLinkSuccess++;
                        $rowIndex++;
                    }
                } elseif (!isset($subjectLinkGroup[$subject['id']]) && $subject) {
                    // 没有宣传链接也要把课题写入文件
                    $subjectSuccess++;
                    if (!empty($subject['product_id'])) {
                        $url = $domain . '/#/' . $site . '/products/fastList?type=id&keyword=' . $subject['product_id'];
                    } else {
                        $url = '';
                    }
                    // 名称
                    $sheet->setCellValue([0 + 1, $rowIndex + 1], $subject['name']);
                    // 版本
                    $sheet->setCellValue([1 + 1, $rowIndex + 1], $subject['version']);
                    // 关键词
                    $sheet->setCellValue([4 + 1, $rowIndex + 1], $subject['keywords']);
                    // 是否有数据
                    $sheet->setCellValue([5 + 1, $rowIndex + 1], !empty($subject['has_cagr']) ? '是' : '否');
                    if (!empty($url)) {
                        // 搜索链接
                        $sheet->setCellValue([2 + 1, $rowIndex + 1], $url);
                        $sheet->getCell([2 + 1, $rowIndex + 1])->getHyperlink()->setUrl($url);
                        $sheet->getStyle([2 + 1, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB('0000FF');
                    }
                    $sheet->setCellValue([6 + 1, $rowIndex + 1], $subject['keywords_cn'] ?? ''); // 关键词(中)
                    $sheet->setCellValue([7 + 1, $rowIndex + 1], $subject['keywords_en'] ?? ''); // 关键词(英)
                    $sheet->setCellValue([8 + 1, $rowIndex + 1], $subject['keywords_jp'] ?? ''); // 关键词(日)
                    $sheet->setCellValue([9 + 1, $rowIndex + 1], $subject['keywords_kr'] ?? ''); // 关键词(韩)
                    $sheet->setCellValue([10 + 1, $rowIndex + 1], $subject['keywords_de'] ?? ''); // 关键词(德)

                    $sheet->setCellValue([11 + 1, $rowIndex + 1], $subject['category_name'] ?? ''); // 行业

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

        // /www/wwwroot/yadmin/admin/public/site/gircn/exportDir
        $uploadFileName = $_POST['fileName'];
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

        $excelData = [];
        $postPlatformData = PostPlatform::query()->select(['id', 'name', 'keywords'])->where('status', 1)->get()->toArray();

        // $reader = ReaderEntityFactory::createXLSXReader($excelPath);
        // $reader->setShouldPreserveEmptyRows(false);
        // $reader->setShouldFormatDates(true);
        // $reader->open($excelPath);

        //读取excel数据
        \PhpOffice\PhpSpreadsheet\Settings::setLibXmlLoaderOptions(LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_BIGLINES | LIBXML_DTDLOAD | LIBXML_DTDATTR);

        $filetype = \PhpOffice\PhpSpreadsheet\IOFactory::identify($excelPath); // 自动识别上传的Excel文件类型
        $xlsReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($filetype);
        // $xlsReader->setReadDataOnly(true);
        $xlsReader->setLoadSheetsOnly(true);
        $spreadsheet = $xlsReader->load($excelPath); //载入excel表格


        $details = [];
        $ingoreDetails = [];
        $failDetails = [];

        $subjectSuccess = 0;
        $subjectIngore = 0;
        $subjectFail = 0;

        // 获取所有工作表的名称
        $sheetNames = $spreadsheet->getSheetNames();
        $accepter = $request->user->id;
        $excelData = [];
        $excelDataArticle = [];
        foreach ($sheetNames as $sheetIndex => $sheetName) {

            $sheet = $spreadsheet->getSheet($sheetIndex);
            // // 查询用户
            // $accepter = User::query()->where('nickname', $sheetName)->value('id');
            // if (!$accepter) {
            //     // $subjectFail++;
            //     $failDetails[] = $space . '【' . $sheetName . '】领取人' . $sheetName . '不存在';
            //     // 
            //     $sheetData = $sheet->toArray();
            //     $subjectFail += count($sheetData) ?? 0;
            //     continue;
            // }
            $prevProductId = 0;
            $prevNewName = '';
            $excelData[$sheetName] = [];
            $excelDataArticle[$sheetName] = [];
            $newsNameArray = [];
            $articleNameArray = [];
            // 原始数据
            $sheetData = $sheet->toArray();
            foreach ($sheetData as $rowKey => $sheetRow) {

                // 帖子标题
                $newsName = $sheetRow[0] ?? '';
                // 读取第三列提取报告id
                $fastLink = $sheetRow[2] ?? '';
                // 读取第四列判断是否为超链接
                $postLink = $sheet->getCell([3 + 1, $rowKey + 1])->getHyperlink()->getUrl();
                if (empty($postLink)) {
                    $postLink = $sheetRow[3] ?? '';
                }

                if ($rowKey == 0 && (empty($fastLink) || strpos($fastLink, 'http') === false) && (empty($postLink) || strpos($postLink, 'http') === false)) {
                    continue;
                }
                // 第五列keywords
                $keywords = $sheetRow[4] ?? '';
                // 过滤-空行
                if (empty($newsName) && empty($fastLink) && empty($postLink)) {
                    continue;
                }

                // 过滤-链接没填的
                if (empty($postLink)) {
                    $subjectFail++;
                    $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . $rowKey . '行】发帖链接未填写';
                    continue;
                }

                $productId = 0;
                // 多种情况
                // 1、有标题、有链接、有报告id - 普通课题
                if (!empty($newsName) && !empty($postLink) && !empty($fastLink)) {
                    if (is_numeric($fastLink)) {
                        $productId = $fastLink;
                        $prevNewName  = $newsName;
                    } elseif (
                        preg_match('/(?:\/reports\/(\d+)(?:\/\$keyword)?)/', $fastLink, $matches) ||
                        preg_match('/[?&]keyword=([^&]+)/', $fastLink, $matches) ||
                        preg_match('/ProductsSearch%5Bname%5D=([^&]*)/', $fastLink, $matches)
                    ) {
                        $productId = $matches[1];
                    } else {
                        $subjectFail++;
                        $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($rowKey + 1) . '行】无法提取报告id';
                        continue;
                    }
                    // 记录该条记录，可能下一条记录为同一组课题
                    $prevProductId = $productId;
                    $prevNewName  = $newsName;
                } elseif (!empty($newsName) && !empty($postLink) && empty($fastLink)) {
                    // 2、有标题、有链接、没有报告id - 观点文章
                    if (!isset($excelDataArticle[$sheetName][$newsName])) {
                        $excelDataArticle[$sheetName][$newsName] = [];
                        $articleNameArray[] = $newsName;
                    }
                    $excelDataArticle[$sheetName][$newsName][] = ['rowKey' => $rowKey + 1, 'keywords' => $keywords, 'link' => $postLink];
                    // 记录该条记录，可能下一条记录为同一组课题
                    $prevProductId = 0; // 没有报告id，重置为0，以此表示为观点文章
                    $prevNewName  = $newsName;
                    continue;
                } elseif (empty($newsName) && empty($fastLink) && !empty($postLink)) {
                    // 3、只有链接
                    // 判断上一条是普通课题还是观点文章
                    if (!empty($prevProductId) && !empty($prevNewName)) {
                        $productId = $prevProductId;
                        $newsName  = $prevNewName;
                    } elseif ($prevProductId == 0 && !empty($prevNewName)) {
                        $excelDataArticle[$sheetName][$prevNewName][] = ['rowKey' => $rowKey + 1, 'link' => $postLink];
                        continue;
                    }
                } else {
                }

                if (empty($productId)) {
                    $subjectFail++;
                    $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($rowKey + 1) . '行】第三列缺少搜索链接或者格式无法提取报告id';
                    continue;
                }

                // if (empty($newsName)) {
                //     $subjectFail++;
                //     $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($rowKey + 1) . '行】第一列缺少标题';
                //     continue;
                // }

                // if (in_array($newsName, $newsNameArray) && !isset($newsNameArray[$productId])) {
                //     $subjectFail++;
                //     $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($rowKey + 1) . '行】第一列标题内部重复';
                //     continue;
                // } elseif (!in_array($newsName, $newsNameArray) && !isset($newsNameArray[$productId])) {
                //     $newsNameArray[$productId] = $newsName;
                // }

                // 
                if (!isset($excelData[$sheetName][$productId])) {
                    $excelData[$sheetName][$productId] = [];
                    $excelData[$sheetName][$productId] = [
                        'newsName' => $newsName,
                        'data' => [],
                    ];
                }
                $excelData[$sheetName][$productId]['data'][] = ['rowKey' => $rowKey + 1, 'link' => $postLink];
            }

            if (count($excelData[$sheetName]) > 0) {

                // 处理每个工作簿的数据
                foreach ($excelData[$sheetName] as $productId => $postLinkGroup) {
                    // if (!$postLinkGroup || count($postLinkGroup) == 0) {
                    //     // 没有链接数据,跳过
                    //     continue;
                    // }

                    $linkData = $postLinkGroup['data'];
                    $newsName = $postLinkGroup['newsName'];

                    $postSubjectData = PostSubject::query()->select(['id', 'accepter', 'name', 'keywords'])->where("product_id", $productId)->first()?->toArray();

                    // if (!$postSubjectData) {
                    //     // 查不到该课题,跳过
                    //     $subjectFail += count($postLinkGroup);
                    //     $failDetails[] = $space . '【' . $sheetName . '】查不到报告id为' . $productId . '的课题';
                    //     continue;
                    // }

                    if ($postSubjectData) {

                        if (!empty($postSubjectData['accepter']) && $accepter != $postSubjectData['accepter']) {
                            // 存在领取人的情况下，领取人不一致,跳过
                            // $subjectFail += count($linkData);
                            // $failDetails[] = $space . '【工作簿：' . $sheetName . ' - 第' . ($linkData[0]['rowKey'] ?? '??') . '行】-课题id【' . $postSubjectData['id'] . '】-报告id【' . $productId . '】领取者不一致';

                            $productData = Products::query()->select(['name', 'keywords'])->where("id", $productId)->first();
                            $tempName = $newsName;
                            $tempKeyword = $postSubjectData['keywords'];
                            if ($productData) {
                                $productData = $productData->toArray();
                                $tempName = $productData['name'];
                                $tempKeyword = $productData['keywords'];
                            }
                            if (isset($excelDataArticle[$sheetName][$tempName])) {
                                $excelDataArticle[$sheetName][$tempName] = array_merge($excelDataArticle[$sheetName][$tempName], $linkData);
                            } else {
                                // return $postSubjectData;
                                $excelDataArticle[$sheetName][$tempName] = $linkData;
                                $excelDataArticle[$sheetName][$tempName][0]['keywords'] = $tempKeyword;
                            }
                            continue;
                        }

                        $urlData = [];
                        $urlData = PostSubjectLink::query()->select(['link'])->where(['post_subject_id' => $postSubjectData['id']])->pluck('link')?->toArray() ?? [];
                        if ($urlData) {
                            $urlData = array_map(function ($urlItem) {
                                $urlItem = trim(trim(trim(trim($urlItem), 'https://'), 'http://'), '/');
                                return $urlItem;
                            }, $urlData);
                        }
                        $isUpdate = false;
                        $existLinkBySubject = [];
                        foreach ($linkData as $postLinkValue) {
                            // 链接一致不变动 新：要求有协议没协议要视为同一个
                            $removeProtocolLink = trim(trim(trim(trim($postLinkValue['link']), 'https://'), 'http://'), '/');

                            if (in_array($removeProtocolLink, $existLinkBySubject)) {
                                //单个课题中链接重复
                                $subjectFail++;
                                $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-课题id【' . $postSubjectData['id'] . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 文件内部同个课题存在一样的链接';
                                continue;
                            }
                            $existLinkBySubject[] = $removeProtocolLink;

                            if (in_array($removeProtocolLink, $urlData)) {
                                $subjectIngore++;
                                $ingoreDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-课题id【' . $postSubjectData['id'] . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 链接已存在';
                                continue;
                            } else {
                                // 获取平台id
                                $postPlatformId = 0;
                                if ($postPlatformData) {
                                    foreach ($postPlatformData as $postPlatformItem) {
                                        if (strpos($postLinkValue['link'], $postPlatformItem['keywords']) !== false) {
                                            $postPlatformId = $postPlatformItem['id'];
                                            break;
                                        }
                                    }
                                } else {
                                    $subjectFail++;
                                    $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】- 发帖平台数据为空';
                                    continue;
                                }
                                if (!isset($postPlatformId) || empty($postPlatformId)) {
                                    $subjectFail++;
                                    $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-课题id【' . $postSubjectData['id'] . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 没有对应平台';
                                    continue;
                                }
                                // 新增
                                $insertChild = [];
                                $insertChild['post_subject_id'] = $postSubjectData['id'];
                                $insertChild['link'] = $postLinkValue['link'];
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

                        $recordUpdate = [];
                        // 如果新增了链接，更新课题时间
                        if ($isUpdate) {
                            $recordUpdate['propagate_status'] = 1;
                            $recordUpdate['last_propagate_time'] = time();
                            if (empty($postSubjectData['accepter'])) {
                                $recordUpdate['accept_time'] = time();
                                $recordUpdate['accept_status'] = 1;
                                $recordUpdate['accepter'] = $accepter;
                            }
                            $recordUpdate['change_status'] = 0;
                        }
                        if (count($recordUpdate) > 0) {
                            PostSubject::query()->where("id", $postSubjectData['id'])->update($recordUpdate);
                        }
                    } else {
                        // 查不到该课题，查询报告存在并新增
                        $productData = Products::query()->select(['id', 'name', 'category_id', 'price', 'author', 'keywords', 'cagr'])->where("id", $productId)->first()?->toArray();
                        if ($productData) {
                            $isInsert = false;
                            //新增课题
                            $recordInsert = [];
                            $recordInsert['product_id'] = $productData['id'];
                            $recordInsert['name'] = $newsName;
                            $recordInsert['product_category_id'] = $productData['category_id'];
                            $recordInsert['version'] =  intval($productData['price'] ?? 0);
                            $recordInsert['analyst'] =  $productData['author'];
                            $recordInsert['accepter'] = $accepter;
                            $recordInsert['accept_time'] = time();
                            $recordInsert['accept_status'] = 1;
                            $recordInsert['keywords'] = $productData['keywords'];
                            $recordInsert['has_cagr'] = !empty($productData['cagr']) ? 1 : 0;
                            $recordInsert['type'] = PostSubject::TYPE_POST_SUBJECT;
                            $postSubjectData = PostSubject::create($recordInsert);

                            //处理链接
                            $existLinkBySubject = [];
                            foreach ($linkData as $postLinkValue) {

                                // 链接一致不变动; 新：要求有协议没协议要视为同一个
                                $removeProtocolLink = trim(trim(trim(trim($postLinkValue['link']), 'https://'), 'http://'), '/');
                                if (in_array($removeProtocolLink, $existLinkBySubject)) {
                                    //单个课题中链接重复
                                    $subjectFail++;
                                    $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-课题id【' . $postSubjectData['id'] . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 文件内部同个课题存在一样的链接';
                                    continue;
                                }
                                $existLinkBySubject[] = $removeProtocolLink;

                                // 获取平台id
                                $postPlatformId = 0;
                                if ($postPlatformData) {
                                    foreach ($postPlatformData as $postPlatformItem) {
                                        if (strpos($postLinkValue['link'], $postPlatformItem['keywords']) !== false) {
                                            $postPlatformId = $postPlatformItem['id'];
                                            break;
                                        }
                                    }
                                } else {
                                    $subjectFail++;
                                    $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】- 发帖平台数据为空';
                                    continue;
                                }
                                if (!isset($postPlatformId) || empty($postPlatformId)) {
                                    $subjectFail++;
                                    $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-课题id【' . $postSubjectData['id'] . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 没有对应平台';
                                    continue;
                                }
                                // 新增
                                $insertChild = [];
                                $insertChild['post_subject_id'] = $postSubjectData['id'];
                                $insertChild['link'] = $postLinkValue['link'];
                                $insertChild['post_platform_id'] = $postPlatformId;
                                $insertChild['status'] = 1;
                                $insertChild['sort'] = 100;
                                $postSubjectLinkModel = new PostSubjectLink();
                                $recordChild = $postSubjectLinkModel->create($insertChild);
                                if ($recordChild) {
                                    $subjectSuccess++;
                                    $isInsert = true;
                                }
                            }

                            $recordInsert = [];
                            if ($isInsert) {
                                $recordInsert['propagate_status'] = 1;
                                $recordInsert['last_propagate_time'] = time();
                                // $recordInsert['accept_time'] = time();
                                // $recordInsert['accept_status'] = 1;
                                // $recordInsert['accepter'] = $accepter;
                                $recordInsert['change_status'] = 0;
                            }
                            if (count($recordInsert) > 0) {
                                PostSubject::query()->where("id", $postSubjectData['id'])->update($recordInsert);
                            }
                        } else {
                            // 查不到该报告,跳过
                            $subjectFail += count($linkData);
                            $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($linkData[0]['rowKey'] ?? '??') . '行】查不到报告id为' . $productId . '的报告，无法新增课题';
                            continue;
                        }
                    }
                }
            }
            $isExistArticleNameArray = [];
            if (count($excelDataArticle[$sheetName]) > 0) {
                // 查看目前观点文章是否存在这些标题
                $articleNameArray = array_keys($excelDataArticle[$sheetName]);
                $isExistArticleArray = PostSubject::query()->select(['id', 'name', 'accepter'])
                    ->whereIn('name', $articleNameArray)
                    ->where('type', PostSubject::TYPE_POST_ARTICLE)
                    ->get()?->toArray() ?? [];
                foreach ($isExistArticleArray as $existArticleItem) {
                    $isExistArticleNameArray[$existArticleItem['name'] . '-' . ($existArticleItem['accepter'] ?? '')] = $existArticleItem;
                }

                foreach ($excelDataArticle[$sheetName] as $articleName => $linkData) {

                    if (isset($isExistArticleNameArray[$articleName . '-' . $accepter])) {
                        $postSubjectData = $isExistArticleNameArray[$articleName . '-' . $accepter];

                        $urlData = [];
                        $urlData = PostSubjectLink::query()->select(['link'])->where('post_subject_id', $postSubjectData['id'])->pluck('link')?->toArray() ?? [];
                        if ($urlData) {
                            $urlData = array_map(function ($urlItem) {
                                $urlItem = trim(trim(trim(trim($urlItem), 'https://'), 'http://'), '/');
                                return $urlItem;
                            }, $urlData);
                        }
                        $isUpdate = false;
                        $existLinkBySubject = []; //单个课题中链接重复

                        foreach ($linkData as $postLinkValue) {
                            // 链接一致不变动; 新：要求有协议没协议要视为同一个
                            $removeProtocolLink = trim(trim(trim(trim($postLinkValue['link']), 'https://'), 'http://'), '/');
                            if (in_array($removeProtocolLink, $existLinkBySubject)) {
                                //单个课题中链接重复
                                $subjectFail++;
                                $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-观点文章id【' . $postSubjectData['id'] . '】' . $postLinkValue['link'] . ' 文件内部同个课题存在一样的链接';
                                continue;
                            }
                            $existLinkBySubject[] = $removeProtocolLink;
                            if (in_array($removeProtocolLink, $urlData)) {
                                $subjectIngore++;
                                $ingoreDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-观点文章id【' . $postSubjectData['id'] . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 链接已存在';
                                continue;
                            } else {
                                // 获取平台id
                                $postPlatformId = 0;
                                if ($postPlatformData) {
                                    foreach ($postPlatformData as $postPlatformItem) {
                                        if (strpos($postLinkValue['link'], $postPlatformItem['keywords']) !== false) {
                                            $postPlatformId = $postPlatformItem['id'];
                                            break;
                                        }
                                    }
                                } else {
                                    $subjectFail++;
                                    $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】- 发帖平台数据为空';
                                    continue;
                                }
                                if (!isset($postPlatformId) || empty($postPlatformId)) {
                                    $subjectFail++;
                                    $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-观点文章id【' . $postSubjectData['id'] . '】' . $postLinkValue['link'] . ' 没有对应平台';
                                    continue;
                                }

                                // 新增
                                $insertChild = [];
                                $insertChild['post_subject_id'] = $postSubjectData['id'];
                                $insertChild['link'] = $postLinkValue['link'];
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

                        $recordUpdate = [];
                        // 如果新增了链接，更新课题时间
                        if ($isUpdate) {
                            $recordUpdate['propagate_status'] = 1;
                            $recordUpdate['last_propagate_time'] = time();
                            if (empty($postSubjectData['accepter'])) {
                                $recordUpdate['accept_time'] = time();
                                $recordUpdate['accept_status'] = 1;
                                $recordUpdate['accepter'] = $accepter;
                            }
                            if (isset($linkData[0]['keywords']) && !empty($linkData[0]['keywords'])) {
                                $recordUpdate['keywords'] = $linkData[0]['keywords'] ?? '';
                            }
                        }
                        if (count($recordUpdate) > 0) {
                            PostSubject::query()->where("id", $postSubjectData['id'])->update($recordUpdate);
                        }
                    } else {

                        // if (!isset($linkData[0]['keywords']) || empty($linkData[0]['keywords'])) {
                        //     $subjectFail += count($linkData);
                        //     //单个课题中链接重复
                        //     $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . (($linkData[0]['rowKey'] + 1) ?? '??') . '行】' . $linkData[0]['link'] . ' 缺少关键词';
                        //     continue;
                        // }
                        //新增观点文章
                        $recordInsert = [];
                        $recordInsert['name'] = $articleName;
                        $recordInsert['type'] = PostSubject::TYPE_POST_ARTICLE;
                        $recordInsert['product_id'] = 0;
                        $recordInsert['keywords'] =  $linkData[0]['keywords'] ?? '';
                        $recordInsert['accepter'] = $accepter;
                        $recordInsert['accept_time'] = time();
                        $recordInsert['accept_status'] = 1;
                        $recordInsert['change_status'] = 0;
                        $recordInsert['has_cagr'] = 0;
                        $postSubjectData = PostSubject::create($recordInsert);

                        $isInsert = false;

                        //处理链接
                        $existLinkBySubject = []; //单个课题中链接重复
                        foreach ($linkData as $postLinkValue) {

                            // 链接一致不变动; 新：要求有协议没协议要视为同一个
                            $removeProtocolLink = trim(trim(trim(trim($postLinkValue['link']), 'https://'), 'http://'), '/');
                            if (in_array($removeProtocolLink, $existLinkBySubject)) {
                                //单个课题中链接重复
                                $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-观点文章id【' . $postSubjectData['id'] . '】' . $postLinkValue['link'] . ' 文件内部同个课题存在一样的链接';
                                continue;
                            }
                            $existLinkBySubject[] = $removeProtocolLink;
                            // 获取平台id
                            $postPlatformId = 0;
                            if ($postPlatformData) {
                                foreach ($postPlatformData as $postPlatformItem) {
                                    if (strpos($postLinkValue['link'], $postPlatformItem['keywords']) !== false) {
                                        $postPlatformId = $postPlatformItem['id'];
                                        break;
                                    }
                                }
                            } else {
                                $subjectFail++;
                                $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】- 发帖平台数据为空';
                                continue;
                            }
                            if (!isset($postPlatformId) || empty($postPlatformId)) {
                                $subjectFail++;
                                $failDetails[] = '【工作簿：' . $sheetName . ' - 第' . ($postLinkValue['rowKey'] ?? '??') . '行】-观点文章id【' . $postSubjectData['id'] . '】' . $postLinkValue['link'] . ' 没有对应平台';
                                continue;
                            }

                            // 新增
                            $insertChild = [];
                            $insertChild['post_subject_id'] = $postSubjectData['id'];
                            $insertChild['link'] = $postLinkValue['link'];
                            $insertChild['post_platform_id'] = $postPlatformId;
                            $insertChild['status'] = 1;
                            $insertChild['sort'] = 100;
                            $postSubjectLinkModel = new PostSubjectLink();
                            $recordChild = $postSubjectLinkModel->create($insertChild);

                            if ($recordChild) {
                                $subjectSuccess++;
                                $isInsert = true;
                            }
                        }

                        $recordInsert = [];
                        if ($isInsert) {
                            $recordInsert['propagate_status'] = 1;
                            $recordInsert['last_propagate_time'] = time();
                        }
                        if (count($recordInsert) > 0) {
                            PostSubject::query()->where("id", $postSubjectData['id'])->update($recordInsert);
                        }
                    }
                }
            }
        }

        $logData = [];
        $logData['file'] = $uploadFileName;
        $logData['type'] = PostSubjectLog::POST_SUBJECT_LINK_UPLOAD;
        // $logData['post_subject_id'] = ;
        $logData['success_count'] = $subjectSuccess;
        $logData['ingore_count'] = $subjectIngore;
        $logData['error_count'] = $subjectFail;

        $logData['details'] = '';
        $logData['details'] .= date('Y-m-d H:i:s', time()) . ' 操作人【' . $request->user->nickname . '】' . "\n";
        $logData['details'] .= '成功导入' . $subjectSuccess . '个链接' . "\n";
        $logData['details'] .= implode("\n", $details) . "\n";

        $logData['ingore_details'] = '';
        $logData['ingore_details'] .= '忽略' . $subjectIngore . '条数据' . "\n";
        $ingoreDetailsText = $logData['ingore_details'];
        $logData['ingore_details'] .= implode("\n", $ingoreDetails) . "\n";

        $logData['error_details'] = '';
        $logData['error_details'] .= '共计' . $subjectFail . '个链接导入失败' . "\n";
        $logData['error_details'] .= implode("\n", $failDetails) . "\n";
        PostSubjectLog::create($logData);

        if (!$excelData || count($excelData) < 1) {
            ReturnJson(FALSE, trans('lang.data_empty'), '上传失败,没数据');
        }
        ReturnJson(true, trans('lang.request_success'), explode("\n", $logData['details'] . $ingoreDetailsText . $logData['error_details']));
    }

    /**
     * 上传旧日志(帖子)
     */
    public function uploadSubjectLinkOld(Request $request)
    {
        $readColumnRule = $request->readColumnRule;
        if (empty($readColumnRule)) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少分割规则');
        }

        // 格式1 B-C-D
        // 格式2 A-B&C-D&E-F

        $readColumn = [];
        $group = explode('&', $readColumnRule);
        foreach ($group as $key => $groupItem) {
            $column = explode('-', $groupItem);

            array_push($readColumn, [
                'title' => $column[0],
                'link' => $column[1],
                'time' => $column[2] ?? null,
                'keywords' => $column[3] ?? null,
            ]);
        }
        return $this->UploadSubjectByName($request, $readColumn, PostSubjectLog::POST_SUBJECT_LINK_UPLOAD_OLD);
    }

    function generateColumnMap()
    {
        $columns = [];
        $index = 0;

        // 生成 A-Z
        for ($i = ord('A'); $i <= ord('Z'); $i++) {
            $columns[chr($i)] = $index;
            $index++;
        }

        // 生成 AA-ZZ
        for ($j = ord('A'); $j <= ord('Z'); $j++) {
            for ($k = ord('A'); $k <= ord('Z'); $k++) {
                $columns[chr($j) . chr($k)] = $index;
                $index++;
            }
        }
        return $columns;
    }

    public function UploadSubjectByName(Request $request, $readColumn = [], $logType = PostSubjectLog::POST_SUBJECT_LINK_UPLOAD)
    {

        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", '-1'); // 不限制内存
        // $time1 = microtime(true);
        $file_temp_name = $_POST['file_temp_name'] ?? null; //随机数，用于建立临时文件夹

        $chunks = $_POST['totalNo'] ?? null; //切片总数
        $currentChunk = $_POST['no'] ?? null; //当前切片
        $blob = $_FILES['file'] ?? null; //二进制数据
        $accepter = $_POST['accepter'];

        if ($file_temp_name === null) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少随机数');
        } elseif ($chunks === null) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少切片总数');
        } elseif ($currentChunk === null) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少当前切片序号');
        } elseif ($blob === null) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少blob数据');
        } elseif (empty($accepter)) {
            ReturnJson(FALSE, trans('lang.param_empty'), '缺少领取人');
        }


        $uploadFileName = $_POST['fileName'];
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

        //读取excel数据
        \PhpOffice\PhpSpreadsheet\Settings::setLibXmlLoaderOptions(LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_BIGLINES | LIBXML_DTDLOAD | LIBXML_DTDATTR);

        $filetype = \PhpOffice\PhpSpreadsheet\IOFactory::identify($excelPath); // 自动识别上传的Excel文件类型
        $xlsReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($filetype);
        // $xlsReader->setReadDataOnly(true);
        $xlsReader->setLoadSheetsOnly(true);
        $spreadsheet = $xlsReader->load($excelPath); //载入excel表格


        // 使用sphinx加快速度
        $conn = null;
        try {
            $conn = (new SphinxService())->getConnection();
        } catch (\Throwable $th) {
            ReturnJson(FALSE, 'sphinx未启动或连接失败', $th->getMessage());
        }

        // 平台信息
        $postPlatformData = PostPlatform::query()->select(['id', 'name', 'keywords'])->where('status', 1)->get()->toArray();
        // 获取所有工作表的名称
        $sheetNames = $spreadsheet->getSheetNames();
        // $accepter = $request->user->id;
        $excelData = [];
        $space = '    ';
        $details = [];
        $failDetails = [];
        $subjectSuccess = 0;
        $subjectFail = 0;
        $columnMap = $this->generateColumnMap();
        foreach ($sheetNames as $sheetIndex => $sheetName) {

            $sheet = $spreadsheet->getSheet($sheetIndex);
            $sheetNameToTime = str_replace('.', '-', $sheetName);
            if (strpos($sheetNameToTime, '2025') === false) {
                $sheetNameToTime = strtotime('2025-' . $sheetNameToTime);
            }
            if (empty($sheetNameToTime)) {
                $sheetNameToTime = time();
            }
            $prevNewName = '';
            $excelData[$sheetName] = [];
            $subjectNameArray = []; // 记录所有课题名称
            // $existPostLinkArray = []; // 记录所有链接
            // 原始数据
            $sheetData = $sheet->toArray();
            foreach ($readColumn as $columnItem) {

                $subjectNameColumn = $columnItem['title'];
                $subjectNameColumnIndex = $columnMap[$subjectNameColumn];
                $subjectLinkColumn = $columnItem['link'];
                $subjectLinkColumnIndex = $columnMap[$subjectLinkColumn];
                // 时间、关键词不一定有
                $subjectTimeColumn = $columnItem['time'];
                $subjectTimeColumnIndex = $subjectTimeColumn ? $columnMap[$subjectTimeColumn] : null;
                $subjectKeywordsColumn = $columnItem['keywords'];
                $subjectKeywordsColumnIndex = $subjectKeywordsColumn ? $columnMap[$subjectKeywordsColumn] : null;

                if (empty($subjectNameColumn) || empty($subjectLinkColumn)) {
                    break;
                }
                foreach ($sheetData as $rowKey => $sheetRow) {

                    // if($rowKey > 1000){
                    //     continue;
                    // }
                    // 帖子标题
                    $subjectName = $sheetRow[$subjectNameColumnIndex] ?? '';
                    // 帖子链接
                    $postLink = $sheet->getCell($subjectLinkColumn . ($rowKey + 1))->getHyperlink()->getUrl();
                    if (empty($postLink)) {
                        // 是否超链接,否则直接读取文本
                        $postLink = $sheetRow[$subjectLinkColumnIndex] ?? '';
                    }
                    // 帖子时间
                    $subjectTime = $subjectTimeColumnIndex && !empty($sheetRow[$subjectTimeColumnIndex]) ? strtotime($sheetRow[$subjectTimeColumnIndex]) : '';
                    $subjectTime = empty($subjectTime) ? $sheetNameToTime : $subjectTime;

                    // 关键词
                    $subjectKeywords = $subjectKeywordsColumnIndex && !empty($sheetRow[$subjectKeywordsColumnIndex]) ? $sheetRow[$subjectKeywordsColumnIndex] : '';

                    $subjectName = trim($subjectName);
                    $postLink = trim($postLink);

                    if (empty($subjectName) && empty($postLink)) {
                        // 算空行跳过
                        continue;
                    }

                    if (empty($postLink)) {
                        $subjectFail++;
                        $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . $subjectLinkColumn . ($rowKey + 1) . '】链接没有填写';
                        continue;
                    }
                    // if (in_array($postLink, $existPostLinkArray)) {
                    //     $subjectFail++;
                    //     $failDetails[] = $space . '【工作簿：' . $sheetName . ' - 第' . ($rowKey + 1) . '行】链接内部重复';
                    //     continue;
                    // }

                    if (empty($subjectName) && !empty($prevNewName)) {
                        // 在链接存在的情况下，如果没有课题名称但是有上一个课题的名称
                        $subjectName = $prevNewName;
                    } elseif (!empty($subjectName)) {
                        // 正常情况下
                        $prevNewName = $subjectName;
                    } else {
                        $subjectFail++;
                        $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . $$subjectLinkColumn . ($rowKey + 1) . '】没有课题名称';
                        continue;
                    }

                    if (!isset($excelData[$sheetName][$subjectName])) {
                        $excelData[$sheetName][$subjectName] = [];
                        $excelData[$sheetName][$subjectName] = [
                            'data' => [],
                            'time' => $subjectTime,
                            'keywords' => $subjectKeywords,
                        ];
                        $subjectNameArray[] = $subjectName;
                    }
                    $excelData[$sheetName][$subjectName]['data'][] = [
                        'rowKey' => $rowKey + 1,
                        'link' => $postLink
                    ];
                }
            }
            if (count($subjectNameArray) == 0) {
                continue;
            }

            // 根据课题标题名称(报告名称)获取报告id
            $productIds = [];
            // foreach ($subjectNameArray as $subjectNameItem) {
            // $query = (new SphinxQL($conn))->select('id')->from('products_rt')->where('name', $subjectNameItem);
            // // $productsQueryResult = $query->compile()->getCompiled();
            // $productsQueryResult = $query->execute();
            // $productQueryData = $productsQueryResult->fetchAllAssoc();
            // $productIds = array_merge($productIds,$productQueryData??[]);
            // }

            $productIds = Products::query()->select(['id'])
                ->whereIn("name", $subjectNameArray)
                ->get()?->toArray() ?? [];

            // return $productIds;
            $productData = [];
            $subjectType = PostSubject::TYPE_POST_SUBJECT;
            $isExistSubjectArray = [];
            if ($productIds) {
                $productIds = array_column($productIds, 'id');
                // 报告数据
                $productData = Products::query()->select(['id', 'name', 'category_id', 'price', 'author', 'keywords', 'cagr'])
                    ->whereIn("id", $productIds)
                    ->get()?->toArray() ?? [];
                $productData = array_column($productData, null, 'name');
                $productIds = array_column($productData, 'id');

                // 课题数据
                $isExistSubjectArray = PostSubject::query()->select(['id', 'name', 'product_id', 'accepter'])
                    ->whereIn("product_id", $productIds)
                    ->where("type", $subjectType)
                    ->get()?->toArray();
                $isExistSubjectArray = $isExistSubjectArray ? array_column($isExistSubjectArray, null, 'product_id') : [];
            }

            $articleDataArray = [];
            foreach ($excelData[$sheetName] as $subjectNameKey => $postLinkGroup) {

                $linkData = $postLinkGroup['data'];

                // 报告是否存在 
                $product = $productData[$subjectNameKey] ?? null;

                if ($product) {
                    $productId = $product['id'];
                    // 课题是否存在
                    $postSubjectData = $isExistSubjectArray[$productId] ?? null;

                    if ($postSubjectData) {
                        $postSubjectId = $postSubjectData['id'];
                        if (!empty($postSubjectData['accepter']) && $accepter != $postSubjectData['accepter']) {
                            // 领取人不一致,跳过
                            $articleDataArray[$subjectNameKey] = $postLinkGroup;
                            continue;
                        }

                        $urlData = [];
                        $urlData = PostSubjectLink::query()->select(['link'])->where(['post_subject_id' => $postSubjectId])->pluck('link')?->toArray() ?? [];
                        if ($urlData) {
                            $urlData = array_map(function ($urlItem) {
                                $urlItem = trim(trim(trim(trim($urlItem), 'https://'), 'http://'), '/');
                                return $urlItem;
                            }, $urlData);
                        }
                        $isUpdate = false;
                        $existLinkBySubject = [];
                        foreach ($linkData as $postLinkValue) {
                            // 链接一致不变动 新：要求有协议没协议要视为同一个
                            $removeProtocolLink = trim(trim(trim(trim($postLinkValue['link']), 'https://'), 'http://'), '/');

                            if (in_array($removeProtocolLink, $existLinkBySubject)) {
                                //单个课题中链接重复
                                $subjectFail++;
                                $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1) . '】-课题id【' . $postSubjectId . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 文件内部同个课题存在一样的链接';
                                continue;
                            }
                            $existLinkBySubject[] = $removeProtocolLink;

                            if (in_array($removeProtocolLink, $urlData)) {
                                $subjectFail++;
                                $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1) . '】-课题id【' . $postSubjectId . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 链接已存在';
                                continue;
                            } else {
                                // 获取平台id
                                $postPlatformId = 0;
                                if ($postPlatformData) {
                                    foreach ($postPlatformData as $postPlatformItem) {
                                        if (strpos($postLinkValue['link'], $postPlatformItem['keywords']) !== false) {
                                            $postPlatformId = $postPlatformItem['id'];
                                            break;
                                        }
                                    }
                                } else {
                                    continue;
                                }
                                if (!isset($postPlatformId) || empty($postPlatformId)) {
                                    $subjectFail++;
                                    $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1)  . '】-课题id【' . $postSubjectId . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 没有对应平台';
                                    continue;
                                }
                                // 新增
                                $insertChild = [];
                                $insertChild['post_subject_id'] = $postSubjectId;
                                $insertChild['link'] = $postLinkValue['link'];
                                $insertChild['post_platform_id'] = $postPlatformId;
                                $insertChild['status'] = 1;
                                $insertChild['sort'] = 100;
                                $insertChild['created_by'] = $accepter;
                                $postSubjectLinkModel = new PostSubjectLink();
                                $recordChild = $postSubjectLinkModel->create($insertChild);
                                if ($recordChild) {
                                    $subjectSuccess++;
                                    $isUpdate = true;
                                }
                            }
                        }

                        $recordUpdate = [];
                        // 如果新增了链接，更新课题时间
                        if ($isUpdate) {
                            $recordUpdate['propagate_status'] = 1;
                            $recordUpdate['last_propagate_time'] = $postLinkGroup['time'];
                            $recordUpdate['type'] = $subjectType;
                            if (empty($postSubjectData['accepter'])) {
                                $recordUpdate['accept_time'] = $postLinkGroup['time'];
                                $recordUpdate['accept_status'] = 1;
                                $recordUpdate['accepter'] = $accepter;
                            }
                            $recordUpdate['change_status'] = 0;
                        }
                        if (count($recordUpdate) > 0) {
                            PostSubject::query()->where("id", $postSubjectId)->update($recordUpdate);
                        }
                    } else {
                        // 没有则新增课题
                        $isInsert = false;
                        $recordInsert = [];
                        $recordInsert['product_id'] = $productId;
                        $recordInsert['name'] = $subjectNameKey;
                        $recordInsert['type'] = $subjectType;
                        $recordInsert['product_category_id'] = $product['category_id'];
                        $recordInsert['version'] =  intval($product['price'] ?? 0);
                        $recordInsert['analyst'] =  $product['author'];
                        $recordInsert['accepter'] = $accepter;
                        $recordInsert['accept_time'] = $postLinkGroup['time'];
                        $recordInsert['accept_status'] = 1;
                        $recordInsert['keywords'] = $product['keywords'];
                        $recordInsert['has_cagr'] = !empty($product['cagr']) ? 1 : 0;
                        $postSubjectData = PostSubject::create($recordInsert);
                        $postSubjectId = $postSubjectData['id'];

                        //处理链接
                        $existLinkBySubject = [];
                        foreach ($linkData as $postLinkValue) {

                            // 链接一致不变动; 新：要求有协议没协议要视为同一个
                            $removeProtocolLink = trim(trim(trim(trim($postLinkValue['link']), 'https://'), 'http://'), '/');
                            if (in_array($removeProtocolLink, $existLinkBySubject)) {
                                //单个课题中链接重复
                                $subjectFail++;
                                $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1) . '】-课题id【' . $postSubjectId . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 文件内部同个课题存在一样的链接';
                                continue;
                            }
                            $existLinkBySubject[] = $removeProtocolLink;

                            // 获取平台id
                            $postPlatformId = 0;
                            if ($postPlatformData) {
                                foreach ($postPlatformData as $postPlatformItem) {
                                    if (strpos($postLinkValue['link'], $postPlatformItem['keywords']) !== false) {
                                        $postPlatformId = $postPlatformItem['id'];
                                        break;
                                    }
                                }
                            } else {
                                continue;
                            }
                            if (!isset($postPlatformId) || empty($postPlatformId)) {
                                $subjectFail++;
                                $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1) . '】-课题id【' . $postSubjectId . '】-报告id【' . $productId . '】' . $postLinkValue['link'] . ' 没有对应平台';
                                continue;
                            }
                            // 新增
                            $insertChild = [];
                            $insertChild['post_subject_id'] = $postSubjectData['id'];
                            $insertChild['link'] = $postLinkValue['link'];
                            $insertChild['post_platform_id'] = $postPlatformId;
                            $insertChild['status'] = 1;
                            $insertChild['sort'] = 100;
                            $insertChild['created_by'] = $accepter;
                            $postSubjectLinkModel = new PostSubjectLink();
                            $recordChild = $postSubjectLinkModel->create($insertChild);
                            if ($recordChild) {
                                $subjectSuccess++;
                                $isInsert = true;
                            }
                        }

                        $recordInsert = [];
                        if ($isInsert) {
                            $recordInsert['propagate_status'] = 1;
                            $recordInsert['last_propagate_time'] = $postLinkGroup['time'];
                            // $recordInsert['accept_time'] = time();
                            // $recordInsert['accept_status'] = 1;
                            // $recordInsert['accepter'] = $accepter;
                            $recordInsert['change_status'] = 0;
                        }
                        if (count($recordInsert) > 0) {
                            PostSubject::query()->where("id", $postSubjectData['id'])->update($recordInsert);
                        }
                    }
                } else {
                    $articleDataArray[$subjectNameKey] = $postLinkGroup;
                    // $subjectFail++;
                    // $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . $titlePositionString . '】找不到相关报告数据';
                    continue;
                }
            }
            // 观点文处理
            if ($articleDataArray && count($articleDataArray) > 0) {

                // 查询重复的观点文
                $articleType = PostSubject::TYPE_POST_ARTICLE;
                $isExistArticleArray = PostSubject::query()->select(['id', 'name', 'accepter'])
                    ->whereIn("name", array_keys($articleDataArray))
                    ->where("type", $articleType)
                    ->get()?->toArray();

                if ($isExistArticleArray) {
                    $isExistArticleArray = array_map(function ($item) {
                        $item['nameWithAccepter'] = $item['name'] . '-' . $item['accepter'];
                        return $item;
                    }, $isExistArticleArray);
                    $isExistArticleArray = $isExistArticleArray ? array_column($isExistArticleArray, null, 'nameWithAccepter') : [];
                }
                // return $isExistArticleArray;
                foreach ($articleDataArray as $subjectNameKey => $postLinkGroup) {
                    $linkData = $postLinkGroup['data'];
                    $articleData = $isExistArticleArray[$subjectNameKey . '-' . $accepter] ?? null;
                    if ($articleData) {
                        $articleId = $articleData['id'];
                        $urlData = [];
                        $urlData = PostSubjectLink::query()->select(['link'])->where(['post_subject_id' => $articleId])->pluck('link')?->toArray() ?? [];
                        if ($urlData) {
                            $urlData = array_map(function ($urlItem) {
                                $urlItem = trim(trim(trim(trim($urlItem), 'https://'), 'http://'), '/');
                                return $urlItem;
                            }, $urlData);
                        }
                        $isUpdate = false;
                        $existLinkBySubject = [];
                        foreach ($linkData as $postLinkValue) {
                            // 链接一致不变动 新：要求有协议没协议要视为同一个
                            $removeProtocolLink = trim(trim(trim(trim($postLinkValue['link']), 'https://'), 'http://'), '/');

                            if (in_array($removeProtocolLink, $existLinkBySubject)) {
                                //单个课题中链接重复
                                $subjectFail++;
                                $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1) . '】-观点id【' . $articleId . '】' . $postLinkValue['link'] . ' 文件内部同个课题存在一样的链接';
                                continue;
                            }
                            $existLinkBySubject[] = $removeProtocolLink;

                            if (in_array($removeProtocolLink, $urlData)) {
                                $subjectFail++;
                                $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1) . '】-观点id【' . $articleId . '】' . $postLinkValue['link'] . ' 链接已存在';
                                continue;
                            } else {
                                // 获取平台id
                                $postPlatformId = 0;
                                if ($postPlatformData) {
                                    foreach ($postPlatformData as $postPlatformItem) {
                                        if (strpos($postLinkValue['link'], $postPlatformItem['keywords']) !== false) {
                                            $postPlatformId = $postPlatformItem['id'];
                                            break;
                                        }
                                    }
                                } else {
                                    continue;
                                }
                                if (!isset($postPlatformId) || empty($postPlatformId)) {
                                    $subjectFail++;
                                    $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1)  . '】-观点id【' . $articleId . '】' . $postLinkValue['link'] . ' 没有对应平台';
                                    continue;
                                }
                                // 新增
                                $insertChild = [];
                                $insertChild['post_subject_id'] = $articleId;
                                $insertChild['link'] = $postLinkValue['link'];
                                $insertChild['post_platform_id'] = $postPlatformId;
                                $insertChild['status'] = 1;
                                $insertChild['sort'] = 100;
                                $insertChild['created_by'] = $accepter;
                                $postSubjectLinkModel = new PostSubjectLink();
                                $recordChild = $postSubjectLinkModel->create($insertChild);
                                if ($recordChild) {
                                    $subjectSuccess++;
                                    $isUpdate = true;
                                }
                            }
                        }

                        $recordUpdate = [];
                        // 如果新增了链接，更新课题时间
                        if ($isUpdate) {
                            $recordUpdate['propagate_status'] = 1;
                            $recordUpdate['last_propagate_time'] = $postLinkGroup['time'];
                            $recordUpdate['type'] = $articleType;
                            if (empty($articleData['accepter'])) {
                                $recordUpdate['accept_time'] = $postLinkGroup['time'];
                                $recordUpdate['accept_status'] = 1;
                                $recordUpdate['accepter'] = $accepter;
                            }
                            $recordUpdate['change_status'] = 0;
                        }
                        if (count($recordUpdate) > 0) {
                            PostSubject::query()->where("id", $articleId)->update($recordUpdate);
                        }
                    } else {
                        // 没有则新增课题
                        $isInsert = false;
                        $recordInsert = [];
                        $recordInsert['product_id'] = 0;
                        $recordInsert['name'] = $subjectNameKey;
                        $recordInsert['accepter'] = $accepter;
                        $recordInsert['accept_time'] = $postLinkGroup['time'];
                        $recordInsert['accept_status'] = 1;
                        $recordInsert['keywords'] = $postLinkGroup['keywords'];
                        $recordInsert['has_cagr'] = 0;
                        $recordInsert['type'] = $articleType;
                        $articleData = PostSubject::create($recordInsert);
                        $articleId = $articleData['id'];

                        //处理链接
                        $existLinkBySubject = [];
                        foreach ($linkData as $postLinkValue) {

                            // 链接一致不变动; 新：要求有协议没协议要视为同一个
                            $removeProtocolLink = trim(trim(trim(trim($postLinkValue['link']), 'https://'), 'http://'), '/');
                            if (in_array($removeProtocolLink, $existLinkBySubject)) {
                                //单个课题中链接重复
                                $subjectFail++;
                                $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1) . '】-观点id【' . $articleId . '】' . $postLinkValue['link'] . ' 文件内部同个课题存在一样的链接';
                                continue;
                            }
                            $existLinkBySubject[] = $removeProtocolLink;

                            // 获取平台id
                            $postPlatformId = 0;
                            if ($postPlatformData) {
                                foreach ($postPlatformData as $postPlatformItem) {
                                    if (strpos($postLinkValue['link'], $postPlatformItem['keywords']) !== false) {
                                        $postPlatformId = $postPlatformItem['id'];
                                        break;
                                    }
                                }
                            } else {
                                continue;
                            }
                            if (!isset($postPlatformId) || empty($postPlatformId)) {
                                $subjectFail++;
                                $failDetails[] = $space . '【工作簿：' . $sheetName . '-' . ($postLinkValue['rowKey'] + 1) . '】-观点id【' . $articleId . '】' . $postLinkValue['link'] . ' 没有对应平台';
                                continue;
                            }
                            // 新增
                            $insertChild = [];
                            $insertChild['post_subject_id'] = $articleData['id'];
                            $insertChild['link'] = $postLinkValue['link'];
                            $insertChild['post_platform_id'] = $postPlatformId;
                            $insertChild['status'] = 1;
                            $insertChild['sort'] = 100;
                            $insertChild['created_by'] = $accepter;
                            $postSubjectLinkModel = new PostSubjectLink();
                            $recordChild = $postSubjectLinkModel->create($insertChild);
                            if ($recordChild) {
                                $subjectSuccess++;
                                $isInsert = true;
                            }
                        }

                        $recordInsert = [];
                        if ($isInsert) {
                            $recordInsert['propagate_status'] = 1;
                            $recordInsert['last_propagate_time'] = $postLinkGroup['time'];
                            $recordInsert['change_status'] = 0;
                        }
                        if (count($recordInsert) > 0) {
                            PostSubject::query()->where("id", $articleData['id'])->update($recordInsert);
                        }
                    }
                }
            }
        }


        $logData = [];
        $logData['file'] = $uploadFileName;
        $logData['type'] = $logType;
        // $logData['post_subject_id'] = ;
        $logData['success_count'] = $subjectSuccess;
        $logData['ingore_count'] = $subjectFail;
        $logData['details'] = '';
        $logData['details'] .= date('Y-m-d H:i:s', time()) . ' 操作人【' . $request->user->nickname . '】' . "\n";
        $logData['details'] .= '-- 成功导入' . $subjectSuccess . '个链接' . "\n";
        $logData['details'] .= implode("\n", $details) . "\n";
        $logData['details'] .= '-- 有' . $subjectFail . '个链接导入失败' . "\n";
        $logData['details'] .= implode("\n", $failDetails) . "\n";
        PostSubjectLog::create($logData);

        if (!$excelData || count($excelData) < 1) {
            ReturnJson(FALSE, trans('lang.data_empty'), '上传失败,没数据');
        }
        ReturnJson(true, trans('lang.request_success'), explode("\n", $logData['details']));
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

    public function setExportBlankRow(Request $request)
    {

        $input = $request->all();
        // ReturnJson(true, trans('lang.update_success'),$input);
        if (!$input || !isset($input['value']) || $input['value'] === null) {
            ReturnJson(false, trans('lang.param_empty'), 'value');
        }
        if (!$input || !$input['key']) {
            ReturnJson(false, trans('lang.param_empty'), 'input');
        }
        if (is_numeric($input['value']) && intval($input['value']) >= 0) {
            $key = $input['key'];
            $value = intval($input['value']);
            $user_id = $request->user->id;
            try {
                $record = PersonalSetting::query()->where(['user_id' => $user_id, 'key' => $key])->first();
                if ($record) {
                } else {
                    $insert = [];
                    $insert['key'] = $key;
                    $insert['user_id'] = $user_id;
                    $insert['value'] = 0;
                    $record = PersonalSetting::create($insert);
                }

                $record->value = $value;

                if (!$record->save()) {
                    ReturnJson(false, trans('lang.update_error'));
                }
                ReturnJson(true, trans('lang.update_success'));
            } catch (\Exception $e) { //$e->getCode()
                ReturnJson(false, $e->getMessage());
            }
        } else {
            ReturnJson(false, trans('lang.update_error'), '输入不是数字');
        }
    }
}
