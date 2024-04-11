<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Country;
use Modules\Admin\Http\Models\DictionaryValue;

class CountryController extends CrudController
{
    
    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            $data = [];
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State','status' => 1], ['sort' => 'ASC']);

            ReturnJson(TRUE, trans('lang.request_success'), $data);
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
            $list = Country::where('status', 1)->select(['id', 'data'])->orderBy('sort', 'asc')->orderBy('id', 'asc')->get()->toArray();

            if ($list && count($list) > 0) {

                foreach ($list as $item) {

                    Country::UpdateToRedis($item);
                    
                }
            }
            // echo '已成功同步：'.$i .' 总数量:'.$count;
            // exit;
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
    
}
