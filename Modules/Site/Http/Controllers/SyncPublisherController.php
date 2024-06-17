<?php
/**
 * SyncPublisherController.php UTF-8
 * 同步出版商控制器
 *
 * @date    : 2024/6/14 17:46 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Site;

class SyncPublisherController extends CrudController {
    public function searchDroplist(Request $request) {
        try {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            //

            $site = $request->header('Site');
            $publisherIds = Site::query()->where('name', $site)->value('publisher_id');
            $publisherIdList = explode(',', $publisherIds);
            $PublisherList = Publisher::query()->whereIn('id', $publisherIdList)->pluck("name" , "id")->toArray();
            $data['publisher'] = convertToFormData($PublisherList);

            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
