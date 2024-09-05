<?php

namespace Modules\Admin\Http\Controllers;

use App\Helper\BtPanel;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Modules\Admin\Http\Models\Database;
use Modules\Admin\Http\Models\Position;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Site;
use Modules\Admin\Http\Models\Language;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Region;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Admin\Http\Models\Server;
use Modules\Admin\Http\Models\SiteUpdateLog;
use Stancl\Tenancy\Facades\Tenancy;
use Tymon\JWTAuth\Facades\JWTAuth;

class SiteController extends CrudController
{
    /**
     * 创建一个站点
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $input = $request->all();
        // 创建者ID
        $input['created_by'] = $request->user->id;
        // 是否生成数据库0不生成，1生成！生成数据库必须是MYSQL的ROOT账号，不是ROOT账号否则无法生成数据库
        $is_create = $request->is_create;
        // is_create不是入库的字段变量所以删除
        unset($request->is_create);
        if (!isset($is_create)) {
            $is_create = 0;
        }
        // 开启事务
        DB::beginTransaction();
        try {
            // 入库site表
            $model = new Site();
            $this->ValidateInstance($request);
            $res = $model->create($input);
            if (!$res) {
                // 回滚事务
                DB::rollBack();
                ReturnJson(false, trans('lang.add_error'));
            }
            //
            $database = Database::where('id', $input['database_id'])->select(
                'ip as db_host',
                'name as db_database',
                'username as db_username',
                'password as db_password'
            )->first()->toArray();
            DB::commit();
            // 创建租户
            $Tenant = new TenantController();
            $res = $Tenant->initTenant(
                $is_create,
                $input['name'],
                $input['domain'],
                $database['db_host'],
                $database['db_database'],
                $database['db_username'],
                $database['db_password'],
                $database['db_port'] ?? 3306
            );
            // if ($res !== true) {
            //     // 回滚事务
            //     DB::rollBack();
            //     ReturnJson(FALSE, $res);
            // }
            // //事务有点问题
            // DB::commit();
            ReturnJson(true, trans('lang.add_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 导入数据库基本的数据库结构/初始化数据库
     *
     * @param Request $request
     */
    public function initDatabase(Request $request)
    {
        $dir = 'admin';
        // 查询当前的租户信息
        $siteId = $request->input('site_id');
        $site = Site::findOrFail($siteId);
        $tenantId = DB::table('domains')->where('domain', $site->domain)->pluck('tenant_id');
        $tenant = Tenancy::find($tenantId);
        // 设置当前租户上下文
        Tenancy::initialize($tenant);
        // 读取 SQL 文件内容
        $basePath = public_path() . '/' . $dir;
        $sqlFilePath = $basePath . '/uploads/sql/init_database.sql';
        $sqlContent = file_get_contents($sqlFilePath);
        // 在租户数据库上运行 SQL
        DB::unprepared($sqlContent);
        // 结束当前租户上下文
        Tenancy::end();
        ReturnJson(true, trans('lang.add_success'));
    }

    /**
     * 远程连接服务器新建站点
     *
     * @param Request $request
     */
    public function createSiteToRemoteServer(Request $request)
    {
        set_time_limit(100);
        $siteId = $request->input('site_id');
        $step = $request->input('step');
        $param = $request->input('param');
        if (!empty($param) && !is_array($param)) {
            $param = $param ? json_decode($param, true) : [];
        }
        // 创建者ID
        $created_by = $request->user->id;
        //获取站点配置
        $site = Site::findOrFail($siteId);
        //获取服务器配置
        $server = Server::find($site->server_id);
        //获取数据库配置
        $database = Database::find($site->database_id);
        $checkParamEmpty = [
            'server_model_empty'             => $server,
            'database_model_empty'           => $database,
            'site_name_empty'                => $site->name ?? '',
            'site_api_repository_empty'      => $site->api_repository ?? '',
            'site_frontend_repository_empty' => $site->frontend_repository ?? '',
            'site_api_path_empty'            => $site->api_path ?? '',
            'site_frontend_path_empty'       => $site->frontend_path ?? '',
            'site_domain_empty'              => $site->domain ?? '',
            'server_ip_empty'                => $server->ip ?? '',
            'server_username_empty'          => $server->username ?? '',
            'server_password_empty'          => $server->password ?? '',
            'server_bt_link_empty'           => $server->bt_link ?? '',
            'server_bt_apisecret_empty'      => $server->bt_apisecret ?? '',
            'database_ip_empty'              => $database->ip ?? '',
            'database_username_empty'        => $database->username ?? '',
            'database_password_empty'        => $database->password ?? '',
        ];
        // 判断参数是否为空
        foreach ($checkParamEmpty as $key => $value) {
            if (empty($value)) {
                ReturnJson(false, !empty(trans('lang.' . $key)) ? trans('lang.' . $key) : trans('lang.param_empty'));
            }
        }
        try {
            $initWebsiteStep = Site::getInitWebsiteStep(true);
            if ($initWebsiteStep['commands'] && in_array($step, $initWebsiteStep['commands'])) {
                $output = Site::executeRemoteCommand($site, $step, $server, $database,  ['created_by' => $created_by]);
            } elseif ($initWebsiteStep['btPanelApi'] && in_array($step, $initWebsiteStep['btPanelApi'])) {
                $option['created_by'] = $created_by;
                if (isset($param['private_key'])) {
                    $option['private_key'] = $param['private_key'];
                }
                if (isset($param['csr'])) {
                    $option['csr'] = $param['csr'];
                }
                $output = Site::invokeBtApi($site, $server, $step, $option);
                // ReturnJson(FALSE, trans('lang.request_success'), $output);
            }
        } catch (\Throwable $th) {
            ReturnJson(false, trans('lang.request_error'), $th->getMessage());
        }
        if (!$output['result']) {
            ReturnJson(false, $output['message'] ?? $output['output']);
        }
        ReturnJson(true, trans('lang.request_success'));
    }

    /**
     * 编辑一个站点
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        $input = $request->all();
        // 创建者ID
        $input['created_by'] = $request->user->id;
        // 开启事务
        DB::beginTransaction();
        try {
            // 入库site表
            $model = new Site();
            $model = $model->findOrFail($input['id']);
            $res = $model->update($input);
            if (!$res) {
                // 回滚事务
                DB::rollBack();
                ReturnJson(false, trans('lang.update_error'));
            }
            $database = Database::where('id', $input['database_id'])->select(
                'ip as db_host',
                'name as db_database',
                'username as db_username',
                'password as db_password'
            )->first();
            if (!$database) {
                DB::rollBack();
                ReturnJson(true, trans('lang.update_error') . ' ' . trans('lang.database_model_empty'));
            } else {
                $database = $database->toArray();
            }
            DB::commit();
            // 更新租户
            $Tenant = new TenantController();
            $res = $Tenant->updateTenant(
                $model->name,
                $input['name'],
                $input['domain'],
                $database['db_host'],
                $database['db_database'],
                $database['db_username'],
                $database['db_password'],
                $database['db_port'] ?? 3306
            );
            // return $res;
            // if ($res !== true) {
            //     // 回滚事务
            //     DB::rollBack();
            //     ReturnJson(FALSE, $res);
            // }
            // DB::commit();
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            // 回滚事务
            // DB::rollBack();
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 远程连接服务器更新站点/拉取代码
     *
     * @param Request $request
     */
    public function updateSiteToRemoteServer(Request $request)
    {
        $siteId = $request->input('site_id');
        // 创建者ID
        $created_by = $request->user->id;
        //获取站点配置
        $site = Site::findOrFail($siteId);
        //获取服务器配置
        $server = Server::find($site->server_id);
        try {
            $output = Site::executeRemoteCommand($site, 'pull_code', $server, null,  ['created_by' => $created_by]);
            if (!$output['result']) {
                ReturnJson(false, $output['output']);
            }
            ReturnJson(true, trans('lang.request_success'), $output);
        } catch (\Throwable $th) {
            ReturnJson(false, trans('lang.request_error'), $th->getMessage());
        }
        ReturnJson(true, trans('lang.request_success'));
    }

    /**
     * 获取提交历史记录列表
     *
     * @param Request $request
     */
    public function CommitHistory(Request $request)
    {
        $siteId = $request->input('site_id');
        $pageNum = $request->input('pageNum');
        $pageSize = $request->input('pageSize');
        $pageNum = !empty($pageNum) ? $pageNum : 1;
        $pageSize = !empty($pageSize) ? $pageSize : 10;
        // 创建者ID
        $created_by = $request->user->id;
        //获取站点配置
        $site = Site::findOrFail($siteId);
        //获取服务器配置
        $server = Server::find($site->server_id);
        try {
            //获取数量
            $commitCountOutput = Site::executeRemoteCommand(
                $site,
                'commit_history_count',
                $server,
                null,
                ['created_by' => $created_by]
            );
            $commitCount = 0;
            if ($commitCountOutput['result']) {
                $commitCount = trim($commitCountOutput['output'], "\n");
            }
            //获取具体内容
            $commitOutput = Site::executeRemoteCommand(
                $site,
                'commit_history', 
                $server,
                null,
                [
                    'created_by' => $created_by,
                    'pageNum' => $pageNum,
                    'pageSize'   => $pageSize,
                ]
            );
            $commitOutput['count'] = $commitCount;
            if (!$commitOutput['result']) {
                ReturnJson(false, $commitOutput['output']);
            }
            ReturnJson(true, trans('lang.request_success'), $commitOutput);
        } catch (\Throwable $th) {
            ReturnJson(false, trans('lang.request_error'), $th->getMessage());
        }
        ReturnJson(true, trans('lang.request_success'));
    }

    /**
     * 是否可更新/可升级
     *
     * @param Request $request
     */
    public function availableUpgrade(Request $request)
    {
        $siteId = $request->input('site_id');
        // 创建者ID
        $created_by = $request->user->id;
        //获取站点配置
        $site = Site::findOrFail($siteId);
        //获取服务器配置
        $server = Server::find($site->server_id);
        try {
            //获取数量
            $output = Site::executeRemoteCommand($site, 'available_pull', $server, null,  ['created_by' => $created_by]);
            // if (!$output['result']) {
            //     ReturnJson(FALSE, $output['output']);
            // }
            ReturnJson(true, trans('lang.request_success'), $output);
        } catch (\Throwable $th) {
            ReturnJson(false, trans('lang.request_error'), $th->getMessage());
        }
        ReturnJson(true, trans('lang.request_success'));
    }

    /**
     * 版本回退、代码回滚
     *
     * @param Request $request
     */
    public function rollbackCode(Request $request)
    {
        $siteId = $request->input('site_id');
        $hash = $request->input('hash');
        // 创建者ID
        $created_by = $request->user->id;
        if (empty($hash)) {
            ReturnJson(false, trans('lang.param_empty'), 'hash');
        }
        //获取站点配置
        $site = Site::findOrFail($siteId);
        //获取服务器配置
        $server = Server::find($site->server_id);
        try {
            //获取数量
            $output = Site::executeRemoteCommand(
                $site,
                'rollback_code',
                $server,
                null,
                ['hash' => $hash, 'created_by' => $created_by]
            );
            if (!$output['result']) {
                ReturnJson(false, $output['output']);
            }
            ReturnJson(true, trans('lang.request_success'), $output);
        } catch (\Throwable $th) {
            ReturnJson(false, trans('lang.request_error'), $th->getMessage());
        }
        ReturnJson(true, trans('lang.request_success'));
    }

    /**
     * 删除一个站点
     */
    public function destroy(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            // 同步删除定时任务
            (new TimedTaskController)->DeleteForSiteId($ids);
            foreach ($ids as $id) {
                $record = $this->ModelInstance()->find($id);
                if ($record) {
                    $record->delete();
                }
            }
            // $Tenant = new TenantController();
            // foreach ($ids as $id) {
            //     $res = $Tenant->destroyTenant($id);
            //     if ($res !== true) {
            //         // 回滚事务
            //         DB::rollBack();
            //         ReturnJson(FALSE, $res);
            //     }
            // }
            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    public function list(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            $is_super = Role::whereIn('id', explode(',', $request->user->role_id))->where('is_super', 1)->count();
            if ($is_super == 0) {
                $roles = Role::whereIn('id', explode(',', $request->user->role_id))->pluck('site_id')->toArray();
                $site_ids = [];
                foreach ($roles as $role) {
                    if (!empty($role)) {
                        $site_ids = array_merge($site_ids, $role);
                    }
                }
                $site_ids = array_unique($site_ids);
                $model->whereIn('id', $site_ids);
            }
            // 总数量
            $count = $model->count();
            // 总页数
            $pageCount = $request->pageSize > 0 ? ceil($count / $request->pageSize) : 1;
            // 当前页码数
            $page = $request->pageNum ? $request->pageNum : 1;
            $pageSize = $request->pageSize ? $request->pageSize : 100;
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            // 数据排序
            $order = $request->order ? $request->order : 'id';
            // 升序/降序
            $sort = (strtoupper($request->sort) == 'ASC') ? 'ASC' : 'DESC';
            $record = $model->select($ModelInstance->ListSelect)->orderBy($order, $sort)->get();
            //查询后的数据处理
            if ($record && count($record) > 0) {
                foreach ($record as $key => $item) {
                    //获取服务器配置
                    $server = Server::find($item->server_id);
                    //获取当前站点仓库的版本hash值
                    $currentHashData = '';
                    if (isset($server->ip)) {
                        $currentHashData = Site::executeRemoteCommand($item, 'current_hash', $server);
                    }
                    $record[$key]['hash'] = '';
                    $record[$key]['hash_sample'] = '';
                    if (isset($currentHashData) && isset($currentHashData['result'])) {
                        $temp_array = explode("\n", $currentHashData['output']);
                        $record[$key]['hash'] = $temp_array[0];
                        $record[$key]['hash_sample'] = $temp_array[1];
                    }
                    //是否可更新
                    $record[$key]['available_pull'] = false;
                    // $availablePullData = Site::executeRemoteCommand($item['id'], 'available_pull');
                    // $record[$key]['available_pull'] = $availablePullData['result'];
                    //最新一条站点更新记录
                    $siteUpdateLog = SiteUpdateLog::where('site_id', $item['id'])->select(
                        ['exec_status', 'updated_at', 'hash', 'hash_sample', 'message', 'output']
                    )->orderBy('id', 'desc')->first();
                    if ($siteUpdateLog) {
                        $siteUpdateLog = $siteUpdateLog->toArray();
                    }
                    $record[$key]['log_exec_status'] = $siteUpdateLog['exec_status_text'] ?? '';
                    $record[$key]['log_updated_at'] = $siteUpdateLog['updated_at'] ?? '';
                    $record[$key]['log_updated_hash'] = $siteUpdateLog['hash'] ?? '';
                    $record[$key]['log_updated_hash_sample'] = $siteUpdateLog['hash_sample'] ?? '';
                    $record[$key]['log_message'] = $siteUpdateLog['message'] ?? '';
                    $record[$key]['log_output'] = $siteUpdateLog['output'] ?? '';
                }
            }
            //表头排序
            $headerTitle = (new ListStyle())->getHeaderTitle(class_basename($ModelInstance::class), $request->user->id);
            $data = [
                'count'       => $count,
                'pageCount'   => $pageCount,
                'page'        => $page,
                'pageSize'    => $pageSize,
                'list'        => $record,
                'headerTitle' => !empty($headerTitle) ? $headerTitle : [],
                'step'        => Site::getInitWebsiteStep(),
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 获取搜索下拉列表
     *
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            $data = [];
            // 语言
            $data['languages'] = (new Language())->GetListLabel(
                ['id as value', 'name as label'],
                false,
                '',
                ['status' => 1]
            );
            // 出版商
            $data['publishers'] = (new Publisher())->GetListLabel(
                ['id as value', 'name as label'],
                false,
                '',
                ['status' => 1]
            );
            // 国家
            $data['countries'] = (new Region())->GetListLabel(
                ['id as value', 'name as label'],
                false,
                '',
                ['status' => 1]
            );
            //数据库
            $data['databases'] = (new Database())->GetListLabel(
                ['id as value', 'name as label'],
                false,
                '',
                ['status' => 1]
            );
            //服务器
            $data['servers'] = (new Server())->GetListLabel(
                ['id as value', 'name as label'],
                false,
                '',
                ['status' => 1]
            );
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field,
                false,
                '',
                ['code' => 'Switch_State', 'status' => 1],
                ['sort' => 'ASC']
            );
            //是否创建数据库
            // $data['is_create_database'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code'=>'Create Database','status' => 1], ['sort' => 'ASC']);
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    // git 命令
    public function git()
    {
        //1.项目目录不对
        //2.已经更细 string(19) "Already up to date."
        //3.有冲突
        // array(1) {
        //     [0]=>
        //     string(25) "Updating 31c0247..644415a"
        //   }
        //   int(1)
        //4.正常更新
        // array(5) {
        //     [0]=>
        //     string(25) "Updating f59d9b0..31c0247"
        //     [1]=>
        //     string(12) "Fast-forward"
        //     [2]=>
        //     string(12) " a.txt | 1 +"
        //     [3]=>
        //     string(31) " 1 file changed, 1 insertion(+)"
        //     [4]=>
        //     string(25) " create mode 100644 a.txt"
        //   }
        //   int(0)
        // array(4) {
        //     [0]=>
        //     string(25) "Updating 31c0247..644415a"
        //     [1]=>
        //     string(12) "Fast-forward"
        //     [2]=>
        //     string(13) " a.txt | 2 +-"
        //     [3]=>
        //     string(46) " 1 file changed, 1 insertion(+), 1 deletion(-)"
        //   }
        //   int(0)
        // 获取项目的根目录路径
        // $RootPath = base_path();
        // $RootPath = 'G:\phpstudy_pro\WWW\MyPor';
        // $exec = "cd ".$RootPath;
        // $exec .= " & git pull";
        // // var_dump($exec);die;
        // exec($exec,$res,$status);
        exec("git pull", $res, $status);
        var_dump($res, $status);
    }

    /**
     * 推送消息到mq中
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveUpSite(Request $request)
    {
        try {
            $className = get_class($this);
            $id = $request->input('id');
            if (!$id) {
                ReturnJson(false, '缺少站点id');
            }
            $info = Site::where('id', $id)->select('id', 'english_name')->first()->toArray();
            $englishName = $info['english_name'];
            $data = json_encode(
                ['class' => 'Modules\Admin\Http\Controllers\SiteController', 'method' => 'message', 'data' => $info]
            );
            ReturnJson(true, '操作成功');
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die;
            // return $this->failed($e->getMessage() . $e->getLine());
            ReturnJson(false, '操作失败');
        }
    }

    /**
     * 异常扑获
     *
     * @param \Exception $exception
     */
    public function failed(\Exception $exception)
    {
        print_r($exception->getMessage());
    }

    /**
     * 分站点接收队列更新指令
     *
     * @param $params
     *
     * @return void
     * @throws \Exception
     */
    public static function message($params = null)
    {
        if (empty($params)) {
            echo ' 我没有参数1 ';
        } else {
            echo ' 我有参数2 ';
            $RootPath = base_path();
            var_dump($params);
            $RootPath = 'D:\phpstudy\phpstudy_pro\WWW\site\siteadmin.qyrdata.com';
            $exec = "cd " . $RootPath;
            $exec .= " & git pull";
            exec($exec, $res, $status);
            var_dump($res, $status);
            $result = [];
            $result['message'] = $res;
            $result['status'] = $status;
            $result['data'] = $params['data'];
            $data = json_encode(
                [
                    'class' => 'Modules\Admin\Http\Controllers\SiteController',
                    'method' => 'callbackResults',
                    'data'  => $result
                ]
            );
        }
    }

    /**
     * git更新完成返回总平台的回调
     *
     * @param $params
     *
     * @return void
     */
    public static function callbackResults($params = null)
    {
        //将数据入库数据表
        $res = SiteUpdateLog::insert(
            [
                'site_id'      => $params['data']['id'],
                'english_name' => $params['data']['english_name'],
                'message'      => $params['message'][0],
                'status'       => $params['status'],
                'created_at'   => time(),
                'updated_at'   => time()
            ],
        );
        if ($res) {
            echo '保存成功';
            //将结果加到缓存
            cache()->put($params['data']['english_name'], [
                'site'         => $params['data']['id'],
                'english_name' => $params['data']['english_name'],
                'message'      => $params['message'][0],
                'status'       => $params['status'],
            ],           600);
        } else {
            echo '保存失败';
        }
    }

    public function setDetail()
    {
        echo '12312312';
    }

    /**
     * 前端持续请求获取缓存更新回调
     */
    public function getCatchGitStatus(Request $request)
    {
        $english = $request->input('english');
        $data = Cache::get($english);
        if (empty($data)) {
            ReturnJson(false, '未获取到信息');
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * Get the current user's site
     *
     * @param int $user_id user id
     */
    public function UserOption(Request $request)
    {
        $res = Role::whereIn('id', explode(',', $request->user->role_id))->where('status', 1)->pluck('site_id')
            ->toArray();
        $site_ids = [];
        foreach ($res as $key => $value) {
            if (is_array($value)) {
                $site_ids = array_merge($site_ids, $value);
            }
        }
        $is_super = Role::whereIn('id', explode(',', $request->user->role_id))->where('is_super', 1)->count();
        $field = $request->HeaderLanguage == 'en' ? ['name as value', 'english_name as label']
            : ['name as value', 'name as label'];
        $res = [];
        if ($is_super > 0) {
            $res = (new Site)->GetListLabel($field, false, '', ['status' => 1]);
        } else {
            $res = (new Site)->GetListLabel($field, false, '', ['status' => 1, 'id' => $site_ids]);
        }
        ReturnJson(true, trans('lang.request_success'), $res);
    }

    public function btTest(Request $request)
    {
        $data = (new BtPanel())->httpToHttps();
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    public function jumpSiteUrl(Request $request) {
        $site = $request->input('site');
        $user = auth()->user();
        $eccryData = [
            $user->id,
            $site,
            time()
        ];
        $encryString = encrypt(implode(',', $eccryData));
        if($site == 'center'){
            $data = [
                'url'   => "https://site.yhresearch.cn/#/control/dashboard",
                'token' => $encryString,
                'site'  => $site,
            ];
        }else {
            if($site == 'qyen') {
                $data = [
                    'url'   => "http://giren.qyrdata.com/#/{$site}/dashboard",
                    'token' => $encryString,
                    'site'  => $site,
                ];
            }else{
                $data = [
                    'url'   => "https://site.yhresearch.cn/#/{$site}/dashboard",
                    'token' => $encryString,
                    'site'  => $site,
                ];
            }
        }

        $domain = $_SERVER['SERVER_NAME'];
        if (strpos($data['url'], $domain) !== false) {
            $data['is_local'] = true;
        }else{
            $data['is_local'] = false;
        }

        ReturnJson(true, trans('lang.request_success'), $data);
    }

    public function decryptSiteToken(Request $request) {
        $site = $request->input('site');
        $token = $request->input('token');

        try{
            $decryptData = decrypt($token);
        }catch (\Exception $e){
            \Log::error('返回结果数据:'.$e->getMessage().'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
            $domian = env('CENTER_DOMAIN' , 'https://site.yhresearch.cn');
            $respData = [
                'code' => true,
                'msg'  => '解析失败!',
                'data' => ['domain' => $domian]
            ];
            ReturnJson(true, '解析失败', $respData);
        }

        $data = explode(',', $decryptData);
        if (count($data) != 3) {
            ReturnJson(false, trans('lang.request_fail'));
        } else {
            $user_id = $data[0];
            $site_id = $data[1];
            $time = $data[2];
            if ($site_id != $site) {
                ReturnJson(false, trans('lang.request_fail'));
            } else {
                if (time() - $time > 50) {
                    ReturnJson(false, trans('lang.request_fail'));
                }
                $user = User::find($user_id);
                if (empty($user)) {
                    ReturnJson(false, trans('lang.request_fail'));
                }
                $respData = $this->getSiteToken($user);
                ReturnJson(true, trans('lang.request_success'), $respData);
            }
        }
        ReturnJson(false, trans('lang.request_fail'));
    }

    public function getSiteToken($user) {
        $tokenKey = 'login_token_'.$user->id;
        $cacheToken = Redis::get($tokenKey);
        if (!empty($cacheToken)) {
            $token = $cacheToken;
            $expires = Redis::ttl($tokenKey);
        } else {
            $token = JWTAuth::fromUser($user);//生成token
            if (!$token) {
                ReturnJson(false, '生成TOKEN失败');
            }
            $user->login_at = time();
            $user->token = $token;
            $user->save();
            $expires = auth('api')->factory()->getTTL() + 66240;
            Redis::setex($tokenKey, $expires, $token);
        }

        return [
            'accessToken'  => $token,
            'expires'      => $expires,
            'refreshToken' => null,
            'tokenType'    => 'Bearer'
        ];
    }
}
