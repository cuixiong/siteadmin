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
                        $urlItem['platform_name'] = $platformList[$urlItem['post_subject_id']] ?? '';
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
            if ($hasChild || $isChange) {
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
            if (count($recordUpdate) > 0) {
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

        foreach ($subjectData as $key => $subject) {
            $rowData = [];
            $rowData[] = $subject['name'];
            $rowData[] = $subject['version'];
            // https://siteadmin.marketmonitorglobal.com.cn/#/gircn/products/fastList?type=id&keyword=2124513
            $rowData[] = $domain . '/#/' . $site . '/products/fastList?type=id&keyword=' . $subject['product_id'];
            $rowData[] = '';

            $rowData = WriterEntityFactory::createRowFromArray($rowData);
            $writer->addRow($rowData);
        }
        $writer->close();
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
        $firstSheetName = '';
        foreach ($subjectGroup as $groupAccepterId => $subjectGroupItem) {
            // 按每个领取人分不同的工作簿
            $sheetName = $accepterList[$groupAccepterId] ?? '';
            if (empty($sheetName)) {
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
                    }
                }
            }
        }
        // return json_encode($a);

        $writer->close();
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
        $reader->setShouldPreserveEmptyRows(true);
        $reader->setShouldFormatDates(true);
        $reader->open($excelPath);
        $excelData = [];

        $platformList = PostPlatform::query()->pluck("name", "id")->toArray();
        
        foreach ($reader->getSheetIterator() as $sheetKey => $sheet) {
            $sheetName = $sheet->getName();
            // 查询用户
            $accepter = User::query()->where('nickname', $sheetName)->value('id');
            if (!$accepter) {
                continue;
            }
            $excelData[$sheetName] = [];
            $prevId = 0;
            foreach ($sheet->getRowIterator() as $rowKey => $sheetRow) {

                if ($rowKey == 1) {
                    continue;
                }
                $tempRow = $sheetRow->toArray();
                $fastLink = $tempRow[2];
                $postLink = $tempRow[3];

                if (empty($fastLink) && !empty($prevId)) {
                    $postId = $prevId;
                } elseif (!empty($fastLink) && preg_match('/[?&]keyword=([^&]+)/', $fastLink, $matches)) {
                    $postId = $matches[1];
                } else {
                    continue;
                }
                if(empty($postId) || empty($postLink)){
                    continue;
                }


                // $tempRow = [
                //     'id' => $postId,
                //     'link' => $postLink,
                // ];
                $excelData[$sheetName][$postId][] = $postLink;
            }

            // 处理每个工作簿的数据
            foreach ($excelData[$sheetName] as $postId => $postLink) {
                
            }


        }
        ReturnJson(true, trans('lang.request_error'), $excelData);


        if (!$excelData || count($excelData) < 1) {
            ReturnJson(FALSE, trans('lang.data_empty'), '没数据');
        }
    }
}
