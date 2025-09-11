<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\SensitiveWordsLog;
use Modules\Site\Services\SenWordsService;

class SensitiveWordsController extends CrudController
{

    public function searchDroplist(Request $request)
    {
        try {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field,
                false,
                '',
                ['code' => 'Switch_State', 'status' => 1],
                ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    protected function store(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();

            // $type = $input['is_count'] ?? 0;
            // $site = $request->header('Site');
            
            // if($type == 1){

            // }elseif($type == 2){
                $record = $this->ModelInstance()->create($input);
                if (!$record) {
                    ReturnJson(false, trans('lang.add_error'));
                }
                (new SenWordsService())->handlerBanByIdList($record->id);

            // }else{
            //     ReturnJson(false, '未传入is_count');
            // }

            ReturnJson(true, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单个更新
     *
     * @param $request 请求信息
     */
    protected function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            $is_update_word = false;
            if ($record->word != $input['word'] || $record->status != $input['status']) {
                $is_update_word = true;
            }
            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            if ($is_update_word) {
                (new SenWordsService())->handlerUnBanByIdList($record->id);
                (new SenWordsService())->handlerBanByIdList($record->id);
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单行删除
     *
     * @param $ids 主键ID
     */
    protected function destroy(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            foreach ($ids as $id) {
                $record = $this->ModelInstance()->find($id);
                if ($record) {
                    $record->delete();
                    (new SenWordsService())->handlerUnBanByIdList($id);
                }
            }
            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 修改状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeStatus(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->status = $request->status;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            if ($record->status == 1) {
                (new SenWordsService())->handlerBanByIdList($record->id);
            } else {
                (new SenWordsService())->handlerUnBanByIdList($record->id);
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function banLogList(Request $request)
    {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $data['info'] = $this->ModelInstance()->findOrFail($request->id);
            $data['ban_log_list'] = SensitiveWordsLog::query()->where('word_id', $request->id)->get()->toArray();
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
