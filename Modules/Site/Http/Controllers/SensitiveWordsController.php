<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\SensitiveWords;
use Modules\Site\Http\Models\SensitiveWordsHandleLog;
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

            $type = $input['is_count'] ?? 0;
            $site = $request->header('Site');
            $word = $input['word'] ?? '';
            if ($type == 1) {
                $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_STORE, $type, $word, $site);
                ReturnJson(true, trans('lang.request_success'), $handleSensitiveRes);
            } elseif ($type == 2) {
                DB::beginTransaction();
                $record = $this->ModelInstance()->create($input);
                $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_STORE, $type, $word, $site);
                if (!$record || !$handleSensitiveRes) {
                    DB::rollBack();
                    ReturnJson(false, trans('lang.add_error'));
                }
                DB::commit();
            } else {
                ReturnJson(false, '未传入is_count');
            }

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
            if ($record->word != $input['word']) {
                $is_update_word = true;
            }

            $type = $input['is_count'] ?? 0;
            $site = $request->header('Site');
            $word = $input['word'] ?? '';
            $oldWord = $record->word;
            if ($type == 1) {

                $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_UPDATE, $type,$word, $site, $is_update_word ? $oldWord : []);
                ReturnJson(true, trans('lang.request_success'), $handleSensitiveRes);
            } elseif ($type == 2) {
                DB::beginTransaction();
                $record->update($input);
                $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_UPDATE, $type, $word, $site, $is_update_word ? $oldWord : []);
                if (!$record || !$handleSensitiveRes) {
                    DB::rollBack();
                    ReturnJson(false, trans('lang.update_error'));
                }
                DB::commit();
            } else {
                ReturnJson(false, '未传入is_count');
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
            $input = $request->all();
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            
            $type = $input['is_count'] ?? 0;
            $site = $request->header('Site');
            $words = SensitiveWords::query()->select(['word'])->whereIn('id', $ids)->pluck('word')->toArray();
            if ($type == 1) {
                $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_DESTORY,$type, [] , $site, $words);
                ReturnJson(true, trans('lang.request_success'), $handleSensitiveRes);
            } elseif ($type == 2) {
                DB::beginTransaction();
                $res = SensitiveWords::whereIn('id', $ids)->delete();
                $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_DESTORY,$type, [] , $site, $words);
                if (!$res || !$handleSensitiveRes) {
                    DB::rollBack();
                    ReturnJson(false, trans('lang.update_error'));
                }
                DB::commit();
            } else {
                ReturnJson(false, '未传入is_count');
            }


            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 批量隐藏敏感报告数据
     *
     */
    protected function hiddenSenProduct(Request $request){
        
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['is_count'] ?? ''; //1：获取数量;2：执行操作
        $site = $request->header('Site');
        
        $model = SensitiveWords::from('sensitive_words as sw');
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty') . ':ids');
            }
            $model = $model->whereIn('sw.id', $ids);
        } else {
            //筛选
            $model = $this->ModelInstance()->HandleSearch($model, $request->search);
        }

        $wordsArray = $model->select(['word'])->pluck('word')->toArray();
        if ($type == 1) {
            $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_BATCH,$type, $wordsArray , $site, []);
            ReturnJson(true, trans('lang.request_success'), $handleSensitiveRes);
        } elseif ($type == 2){
            $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_BATCH,$type, $wordsArray , $site, []);
            ReturnJson(true, trans('lang.request_success'),);
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

            
            $type = $input['is_count'] ?? 0;
            $site = $request->header('Site');
            $word = $record->word;
            if ($type == 1) {
                if ($record->status == 1){
                    $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_CHANGE_STATUS,$type,$word, $site, []);
                }else{
                    $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_CHANGE_STATUS,$type,[], $site, $word);
                }
                ReturnJson(true, trans('lang.request_success'), $handleSensitiveRes);
            } elseif ($type == 2) {
                DB::beginTransaction();
                $record->save();
                if ($record->status == 1){
                    $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_CHANGE_STATUS, $type,$word, $site, []);
                }else{
                    $handleSensitiveRes = (new SenWordsService())->hiddenData(SensitiveWordsHandleLog::SENSITIVE_WORDS_CHANGE_STATUS, $type,[], $site, $word);
                }
                if (!$record || !$handleSensitiveRes) {
                    DB::rollBack();
                    ReturnJson(false, trans('lang.update_error'));
                }
                DB::commit();
            } else {
                ReturnJson(false, '未传入is_count');
            }

            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

}
