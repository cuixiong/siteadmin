<?php
/**
 * BanWhiteListController.php UTF-8
 * 封禁白名单列表
 *
 * @date    : 2024/10/23 14:29 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\BanWhiteList;
use Modules\Site\Http\Models\Pay;

class BanWhiteListController extends CrudController {
    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    public function list(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            $type = $request->type ?? 1;
            $model = $model->where("type" , $type);
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
                //$model = $model->orderBy('id', 'desc');
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $record = $model->get()->toArray();
            foreach ($record as &$v){
                $v['ban_list'] = @json_decode($v['ban_str'], true);
                $v['ban_list'] = implode("\n", $v['ban_list']);
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

    public function searchDroplist() {
        $data['type'] = [
            '1' => 'IP封禁白名单',
            '2' => 'UA封禁白名单',
        ];
        // 状态开关
        $field = ['name as label', 'value'];
        $data['status'] = (new DictionaryValue())->GetListLabel(
            $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
        );
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    protected function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $remark = $input['remark'];
            $status = $input['status'] ?? 1;
            $sort = $input['sort'] ?? 100;
            $type = $input['type'] ?? 1;
            $banWhiteId = BanWhiteList::query()->where('remark', $remark)->value('id');
            $whiteIpList = @json_decode($input['ban_str'], true);
            if (empty($banWhiteId)) {
                $addWhiteData = [
                    'type'    => $type,
                    'status'    => $status,
                    'sort'    => $sort,
                    'ban_str' => json_encode($whiteIpList),
                    'remark'  => $remark,
                ];
                $record = BanWhiteList::create($addWhiteData);
            } else {
                $banwhiteInfo = BanWhiteList::find($banWhiteId);
                $dbWhiteIpList = @json_decode($banwhiteInfo->ban_str, true);
                $whiteIpList = array_merge($dbWhiteIpList, $whiteIpList);
                $whiteIpList = array_unique($whiteIpList);
                $banwhiteInfo->ban_str = json_encode($whiteIpList);
                $record = $banwhiteInfo->save();
            }
            if (empty($record)) {
                ReturnJson(false, trans('lang.add_error'));
            }
            ReturnJson(true, trans('lang.add_success'), []);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单个查询
     *
     * @param $request 请求信息
     */
    protected function form(Request $request) {
        try {
            $this->ValidateInstance($request);
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record['ban_list'] = json_decode($record['ban_str'], true);
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
