<?php
namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
class EmailController extends CrudController{
    /**
     * 查询value-label格式列表
     * @param $request 请求信息
     * @param Array $where 查询条件数组 默认空数组
     */
    public function optionEmail (Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $record = $ModelInstance->GetListLabel(['id as value','email as label']);
            ReturnJson(TRUE,trans('lang.request_success'),$record);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}