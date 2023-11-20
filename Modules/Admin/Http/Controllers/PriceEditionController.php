<?php

namespace Modules\Admin\Http\Controllers;

use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\PriceEdition;
use Modules\Admin\Http\Models\PriceEditionValue;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Language;
use Modules\Admin\Http\Models\DictionaryValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Requests\PriceEditionValueRequest;

class PriceEditionController extends CrudController
{


    /**
     * 新增价格版本及其子项
     * @param $request 请求信息
     */
    protected function store(Request $request)
    {
        // 开启事务
        DB::beginTransaction();
        try {
            $input = $request->all();
            $this->ValidateInstance($request);
            $model = new PriceEdition();
            $res = $model->create($input);
            if (!$res) {
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.add_error'));
            }
            $editionId = $res['id'];
            $editionData = $input['edition_data'] ?? null;
            if ($editionId && $editionData) {
                $editionData = json_decode($editionData, true);
                //新增价格版本子项
                foreach ($editionData as  $item) {
                    $item['edition_id'] = $editionId;
                    //子项验证
                    (new PriceEditionValueRequest())->store(new Request($item));

                    $itemModel = new PriceEditionValue();
                    $resItem = $itemModel->create($item);
                    if (!$resItem) {
                        // 回滚事务
                        DB::rollBack();
                        ReturnJson(FALSE, trans('lang.add_error'));
                    }
                }
            }
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
        DB::commit();
        ReturnJson(TRUE, trans('lang.add_success'));
    }


    /**
     * 编辑价格版本或其子项
     * @param Request $request
     */
    public function update(Request $request)
    {

        // 开启事务
        DB::beginTransaction();
        try {
            $input = $request->all();

            $this->ValidateInstance($request);

            $model = new PriceEdition();
            $model = $model->findOrFail($input['id']);
            $res = $model->update($input);
            if (!$res) {
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            $editionId = $model->id;
            $editionData = $input['edition_data'] ?? null;
            if ($editionId && $editionData) {
                $editionData = json_decode($editionData, true);
                //删除旧版本项
                PriceEditionValue::where('edition_id', $editionId)->delete();

                //新增版本项
                foreach ($editionData as $item) {
                    $item['edition_id'] = $editionId;
                    //子项验证
                    (new PriceEditionValueRequest())->store(new Request($item));

                    $itemModel = new PriceEditionValue();
                    $resItem = $itemModel->create($item);
                    if (!$resItem) {
                        // 回滚事务
                        DB::rollBack();
                        ReturnJson(FALSE, trans('lang.update_error'));
                    }
                }
            }
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
        DB::commit();
        ReturnJson(TRUE, trans('lang.update_success'));
    }


    /**
     * 删除一个/多个价格版本
     * @param Request $request
     */
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            if (empty($request->ids)) {
                ReturnJson(FALSE, '请输入需要删除的ID');
            }
            $record = $this->ModelInstance()->query();
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            $record->whereIn('id', $ids);
            if (!$record->delete()) {
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE, trans('lang.delete_error'));
            }
            //删除子项
            PriceEditionValue::whereIn('edition_id', $ids)->delete();

            DB::commit();
            ReturnJson(TRUE, trans('lang.delete_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $pageSize 页数
     * @param Array $where 查询条件数组 默认空数组
     */
    public function list(Request $request)
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
            // 数据排序
            $order = $request->order ? $request->order : 'id';
            // 升序/降序
            $sort = (strtoupper($request->sort) == 'ASC') ? 'ASC' : 'DESC';
            $record = $model->select($ModelInstance->ListSelect)->orderBy($order, $sort)->get();

            //查询后的数据处理
            if ($record && count($record) > 0) {

                foreach ($record as $key => $item) {
                    //子项数据
                    $record[$key]['items'] = PriceEditionValue::select('name', 'language_id', 'rules', 'notice', 'is_logistics', 'status', 'sort')
                    ->where('edition_id', $item['id'])
                    ->orderBy('sort', 'ASC')
                    ->get();
                }
            }
            $data = [
                'total' => $total,
                'list' => $record
            ];
            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
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
            // 出版商
            $data['publishers'] = (new Publisher())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);

            // 语言
            $data['languages'] = (new Language())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]);

            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $filed = ['english_name as label', 'value'];
            } else {
                $filed = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Switch_State','status' => 1]);

            //是否送货
            $data['logistics'] = (new DictionaryValue())->GetListLabel($filed, false, '', ['code' => 'Logistics_State','status' => 1]);

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
