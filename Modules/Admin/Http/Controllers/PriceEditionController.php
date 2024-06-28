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
use Modules\Admin\Http\Models\ListStyle;

class PriceEditionController extends CrudController
{

    // 全量更新
    protected function test(Request $request)
    {
        return PriceEdition::SaveToSite(PriceEdition::SAVE_TYPE_FULL, null, true);
    }

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
            $editionToRedisData = [];// 有了事务，就必须做容器保存需要入库的redis的数据
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
                    $editionToRedisData[] = $resItem;
                }
            }
            DB::commit();

            // 同步到分站点
            PriceEdition::SaveToSite(PriceEdition::SAVE_TYPE_FULL, NULL, true);

            ReturnJson(TRUE, trans('lang.add_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 编辑价格版本或其子项
     * @param Request $request
     */
    public function update(Request $request)
    {
        // Site
        // $model = Site::where('status',1)->whereRaw("FIND_IN_SET(?, publisher_id) > 0", [$search->publisher_id]);

        // 开启事务
        DB::beginTransaction();
        try {
            $input = $request->all();
            $this->ValidateInstance($request);

            $model = new PriceEdition();
            $model = $model->findOrFail($input['id']);

            // $newPublisherId = explode(',',$input['publisher_id']);
            // $oldPublisherId = explode(',',$model->publisher_id);
            // $oublisherIdArray = array_unique(array_merge($newPublisherId,$oldPublisherId));

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

                // 需要更新的id
                // $editionDataIds = array_column($editionData,'id');
                $editionDataIds = [];
                foreach ($editionData as $item) {
                    if (isset($item['id']) && !empty($item['id'])) {
                        array_push($editionDataIds,$item['id']);
                    }
                }
                // 数据库存在的id
                $existIds = PriceEditionValue::query()->select('id')->where(['edition_id' => $editionId])->pluck('id')->toArray();
                // 删除多余版本
                $deletedIds = array_values(array_diff($existIds, $editionDataIds));
                if(count($deletedIds)>0){
                    $deleteRecord = PriceEditionValue::query()->whereIn('id', $deletedIds);
                    $deleteRecord->delete();
                }


                $editionToRedisData = [];// 有了事务，就必须做容器保存需要入库的redis的数据
                foreach ($editionData as $item) {

                    $item['edition_id'] = $editionId;
                    if (isset($item['id']) && !empty($item['id'])) {
                        (new PriceEditionValueRequest())->update(new Request($item));

                        $itemModel = PriceEditionValue::find($item['id']);
                        if($itemModel){
                            $resItem = $itemModel->update($item);
                            $editionToRedisData[] = $itemModel;
                        }
                    } else {
                        //子项验证
                        (new PriceEditionValueRequest())->store(new Request($item));

                        $itemModel = new PriceEditionValue();
                        $resItem = $itemModel->create($item);
                        $editionToRedisData[] = $resItem;
                    }

                    if (!isset($resItem) || !$resItem) {
                        // 回滚事务
                        DB::rollBack();
                        ReturnJson(FALSE, trans('lang.update_error'));
                    }

                }
            }
            DB::commit();

            // 同步到分站点
            PriceEdition::SaveToSite(PriceEdition::SAVE_TYPE_FULL, NULL, true);
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE, $e->getMessage());
        }
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

            // 同步到分站点
            PriceEdition::SaveToSite(PriceEdition::SAVE_TYPE_FULL, NULL, true);

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
                    $record[$key]['items'] = PriceEditionValue::select('id', 'name', 'language_id', 'rules', 'notice', 'is_logistics', 'status', 'sort' , 'bind_id')
                        ->where('edition_id', $item['id'])
                        ->orderBy('sort', 'ASC')
                        ->get();
                }
            }
            //表头排序
            $headerTitle = (new ListStyle())->getHeaderTitle(class_basename($ModelInstance::class), $request->user->id);
            $data = [
                'total' => $total,
                'list' => $record,
                'headerTitle' => $headerTitle ?? [],
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
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']);

            //是否送货
            $data['logistics'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Logistics_State', 'status' => 1], ['sort' => 'ASC']);

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
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
            // 同步到分站点
            PriceEdition::SaveToSite(PriceEdition::SAVE_TYPE_FULL, NULL, true);
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    /**
     * 更新全部的价格版本到Redis中
     */
    public function ToRedis(Request $request)
    {
        try {
            $list = PriceEditionValue::get();
            $count = PriceEditionValue::count();
            $i = 0;
            foreach ($list as $key => $value) {
                $res = PriceEditionValue::UpdateToRedis($value);
                if($res == true){
                    $i = $i + 1;
                }
            }
            $langauges = Language::get();
            foreach ($langauges as $key => $value) {
                Language::UpdateToRedis($value);
            }
            $priceEditions = PriceEdition::get();
            foreach ($priceEditions as $key => $value) {
                PriceEdition::UpdateToRedis($value);
            }
            echo '已成功同步：'.$i .' 总数量:'.$count;
            exit;
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

}
