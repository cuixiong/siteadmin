<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\User;

class QuestionsController extends CrudController {
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

            $userList = User::query()->where('status', ">", 0)
                            ->where('check_email', 1)
                            ->selectRaw('username as label,id as value')
                            ->get()->toArray();
            $data['user_list'] = $userList;

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

}
