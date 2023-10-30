<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;

class DictionaryValueController extends CrudController{
    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $pageSize 页数
     * @param Array $where 查询条件数组 默认空数组
     */
    protected function get (Request $request) {
        try {
            if(!empty($request->code)){
                $where['code'] = $request->code;
            }
            if($request->HeaderLanguage == 'en'){
                $filed = ['name as label','value'];
            } else {
                $filed = ['english_name as label','value'];
            }
            $list = (new DictionaryValue())->GetList($filed,false,'',$where);
            ReturnJson(TRUE,'请求成功',$list);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
        }
    }
}
