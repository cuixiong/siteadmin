<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Country;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\PostSubjectFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PostSubjectFilterController extends CrudController
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

        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", '1024M');
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
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $record = $model->get();
            if ($record) {
                $record = $record->toArray();
                $userIds = array_column($record, 'user_id');
                $userNameData = User::query()->select(['id', 'nickname'])->whereIn('id', $userIds)->get();
                if ($userNameData) {
                    $userNameData = $userNameData->toArray();
                    $userNameData = array_column($userNameData, 'nickname', 'id');
                } else {
                    $userNameData = [];
                }
                foreach ($record as $key => $item) {

                    $record[$key]['username'] = $userNameData[$item['user_id']] ?? [];
                }
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

    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            $data = [];

            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);


            // 领取人/发帖用户
            $data['accepter_list'] = (new TemplateController())->getSitePostUser();
            // if (count($data['accepter_list']) > 0) {
            //     array_unshift($data['accepter_list'], ['label' => '公客', 'value' => '-1']);
            // }

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 导出黑名单
     */
    public function export(Request $request)
    {

        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", '-1'); // 不限制内存
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作

        $model = PostSubjectFilter::query();
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty') . ':ids');
            }
            $model = $model->whereIn('id', $ids);
        } else {
            //筛选
            $search = $request->input('search') ?? [];
            $model = (new PostSubjectFilter())->HandleSearch($model, $search);
        }

        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            // //查询出涉及的id
            // $idsData = $model->select('id')->pluck('id')->toArray();
            $filterData = $model->select([
                'id',
                'user_id',
                'keywords',
                'created_at',
            ])
                ->get()->toArray();
            if (!(count($filterData) > 0)) {
                ReturnJson(true, trans('lang.data_empty'));
            }
        }
        $date = date('Ymd', time());
        $excelHeader = [
            '编号',
            '用户',
            '关键词',
            '创建时间',
        ];

        // 创建 Spreadsheet 对象
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置标题行
        $sheet->fromArray([$excelHeader], null, 'A1');

        // 填充数据
        $rowIndex = 1;
        $sheet->getColumnDimension('A')->setWidth(20);  // 设置 A 列宽度
        $sheet->getColumnDimension('B')->setWidth(30);  // 设置 B 列宽度
        $sheet->getColumnDimension('C')->setWidth(40);  // 设置 C 列宽度
        $sheet->getColumnDimension('D')->setWidth(30);  // 设置 D 列宽度

        $domain = env('APP_DOMAIN');
        $site = request()->header("Site");

        $userIds = array_column($filterData, 'user_id');
        $userNameData = User::query()->select(['id', 'nickname'])->whereIn('id', $userIds)->get();
        if ($userNameData) {
            $userNameData = $userNameData->toArray();
            $userNameData = array_column($userNameData, 'nickname', 'id');
        } else {
            $userNameData = [];
        }

        foreach ($filterData as $item) {

            $sheet->setCellValue([1, $rowIndex + 1], $item['id']);
            $username = $userNameData[$item['user_id']] ?? '';
            $sheet->setCellValue([2, $rowIndex + 1], $username);
            // keywords 带后台跳转链接
            $keywordsUrl = '';
            if (!empty($item['keywords'])) {
                $keywordsUrl = $domain . '/#/' . $site . '/products/fastList?type=name&keyword=' . $item['keywords'];
            }
            if (!empty($keywordsUrl)) {
                // 设置超链接
                $sheet->setCellValue([3, $rowIndex + 1], $item['keywords']);
                $sheet->getCell([3, $rowIndex + 1])->getHyperlink()->setUrl($keywordsUrl);
                $sheet->getStyle([3, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB('0000FF');
            }
            $sheet->setCellValue([4, $rowIndex + 1], $item['created_at']);

            $rowIndex++;
        }

        // 设置 HTTP 头部并导出文件
        $date = date('Ymd');
        $filename = 'export-filter-' . count($filterData) . '-' . $date . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }
}
