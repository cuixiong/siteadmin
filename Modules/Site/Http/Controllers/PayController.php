<?php

namespace Modules\Site\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
class PayController extends CrudController
{

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
