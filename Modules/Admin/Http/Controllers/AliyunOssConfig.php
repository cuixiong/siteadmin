<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
class AliyunOssConfig extends CrudController
{
    public function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $count = $this->ModelInstance()->where('site_id', $request->site_id)->count();
            if($count > 0){
                ReturnJson(FALSE, trans('lang.aliyun_oss_config_exist'));
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
