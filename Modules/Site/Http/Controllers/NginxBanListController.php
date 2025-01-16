<?php
/**
 * NginxBanListController.php UTF-8
 * nginx封禁列表
 *
 * @date    : 2025/1/10 17:17 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Controllers;

use App\Console\Commands\CheckNginxLoadCommand;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\NginxBanList;
use Modules\Site\Http\Models\SystemValue;

class NginxBanListController extends CrudController {
    public function searchDroplist() {
        $data['type'] = [
            '1' => 'IP封禁',
            '2' => 'UA封禁',
        ];
        // 状态开关
        $field = ['name as label', 'value'];
        $data['status'] = (new DictionaryValue())->GetListLabel(
            $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
        );
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    public function unBan(Request $request) {
        //解除封禁
        try {
            $ids = $request->input('ids', '');
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            $nginx_ban_model = $this->ModelInstance();
            foreach ($ids as $id) {
                $record = $nginx_ban_model->find($id);
                if ($record) {
                    //设置解封时间
                    $upd_data = [];
                    $upd_data['status'] = 0; //0为解封
                    $upd_data['unban_time'] = time();
                    $nginx_ban_model->where('id', $id)->update($upd_data);
                }
            }
            (new CheckNginxLoadCommand())->reloadNginxBySite(getSiteName());
            ReturnJson(true, trans('lang.request_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function blackList() {
        try {
            $sysValList = SystemValue::query()->where("alias", 'nginx_ban_rules')->pluck('value', 'key')->toArray();
            $black_ban_cnt = $sysValList['black_ban_cnt'] ?? 1;
            $query = NginxBanList::query()->where("status", 1)
                                 ->groupBy('ban_str')
                                 ->having('cnt', '>=', $black_ban_cnt)
                                 ->selectRaw('count(*) as cnt, ban_str , max(created_at) as created_at');
            $total = $query->count();
            // 查询偏移量
            $request = request();
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $query = $query->offset(($request->pageNum - 1) * $request->pageSize)->limit($request->pageSize);
            }
            $list = $query->get()->toArray();
            $data['total'] = $total;
            $data['list'] = $list;
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function delBlack(Request $request) {
        try {
            $ban_str = $request->input('ban_str', []);
            if (empty($ban_str)) {
                ReturnJson(false, 'ban_str 不能为空');
            }
            if (!is_array($ban_str)) {
                $ban_str[] = $ban_str;
            }
            NginxBanList::query()->whereIn("ban_str", $ban_str)->delete();
            (new CheckNginxLoadCommand())->reloadNginxBySite(getSiteName());

            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
