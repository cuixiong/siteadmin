<?php

namespace Modules\Site\Http\Controllers;
use App\Const\PayConst;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
class PayController extends CrudController
{

    public function searchDroplist(Request $request) {
        try {
            $data = [];
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State','status' => 1], ['sort' => 'ASC']);
            $coinTypeList = [];
            $coinTypeMap = PayConst::$coinTypeMap;
            foreach ($coinTypeMap as $key => $value){
                $coinTypeList[] = ['label' => $value, 'value' => $key];
            }
            $data['coin_type'] = $coinTypeList;

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
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
            $sign = request()->input('sign', '');
            if (empty($sign )) {
                $input['sign'] = '';
            }
            $return_url = request()->input('return_url', '');
            if(empty($return_url )){
                $input['return_url'] = '';
            }
            $notify_url = request()->input('notify_url', '');
            if(empty($notify_url )){
                $input['notify_url'] = '';
            }

            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                ReturnJson(false, trans('lang.add_error'));
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
    protected function update(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();

            $sign = request()->input('sign', '');
            if (empty($sign )) {
                $input['sign'] = '';
            }
            $return_url = request()->input('return_url', '');
            if(empty($return_url )){
                $input['return_url'] = '';
            }
            $notify_url = request()->input('notify_url', '');
            if(empty($notify_url )){
                $input['notify_url'] = '';
            }

            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


}
