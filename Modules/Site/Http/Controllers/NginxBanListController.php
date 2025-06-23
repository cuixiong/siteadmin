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

use App\Console\Commands\CheckAccessCntBanCommand;
use App\Console\Commands\CheckNginxLoadCommand;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\NginxBanList;
use Modules\Site\Http\Models\SystemValue;

class NginxBanListController extends CrudController {


    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    protected function list(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            if (empty($request->service_type)) {
                $request->service_type = 1;
            }
            //过滤业务类型
            $model = $model->where("service_type", $request->service_type);

            // 总数量
            $total = $model->count();
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $record = $model->get();
            $data = [
                'total' => $total,
                'list'  => $record
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


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

    public function blackList(Request $request) {
        try {
            $sysValList = SystemValue::query()->where("alias", 'nginx_ban_rules')->pluck('value', 'key')->toArray();
            $black_ban_cnt = $sysValList['black_ban_cnt'] ?? 1;
            $query = NginxBanList::query()->where("status", 1);
            if (empty($request->service_type)) {
                $request->service_type = 1;
            }
            //过滤业务类型
            $query = $query->where("service_type", $request->service_type);
            $search = $request->search ?? '';
            if (!is_array($search)) {
                $search = json_decode($search, true);
                if (!empty($search['created_at'])) {
                    $query = $query->whereBetween('created_at', $search['created_at']);
                }
                if (!empty($search['ban_str'])) {
                    $query = $query->where("ban_str", $search['ban_str']);
                }
            }
            $query = $query->groupBy('ban_str')
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

    public function bandetail(Request $request) {
        try{
            $ban_str = $request->input('ban_str', '');
            if (empty($ban_str)) {
                ReturnJson(false, 'ban_str 不能为空');
            }
            if (empty($request->service_type)) {
                $request->service_type = 1;
            }
            $ban_detail_list = NginxBanList::query()->where("service_type", $request->service_type)
                ->where("ban_str", 'like' , "%{$ban_str}%")->get()->toArray();
            ReturnJson(true, trans('lang.request_success'), $ban_detail_list);
        }catch (\Exception $e){
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
            $query = NginxBanList::query();
            if (empty($request->service_type)) {
                $request->service_type = 1;
            }
            //过滤业务类型
            foreach ($ban_str as $ban_str_item){
                NginxBanList::query()
                    ->where("service_type", $request->service_type)
                    ->where("ban_str", 'like' , "%{$ban_str_item}%")
                    ->delete();
            }

            if($request->service_typ == 1){
                (new CheckNginxLoadCommand())->reloadNginxBySite(getSiteName());
            }else{
                (new CheckAccessCntBanCommand())->delBanStrList(getSiteName());
            }

            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
