<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Country;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\PostSubjectFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PostSubjectFilterController extends CrudController
{

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
            $search = $request->input('search')??[];
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

        foreach ($filterData as $item) {

            $sheet->setCellValue([1, $rowIndex + 1], $item['id']);
            $sheet->setCellValue([2, $rowIndex + 1], $item['user_id']);
            $sheet->setCellValue([3, $rowIndex + 1], $item['keywords']);
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
