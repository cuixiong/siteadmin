<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\RabbitmqService;
use Modules\Admin\Http\Models\Position;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Site;
use Modules\Admin\Http\Models\SiteUpdateLog;

class SiteController extends CrudController
{
    /**
     * 创建一个站点
     * @param Request $request
     */
    public function store(Request $request)
    {

        $input = $request->all();
        /**
         * 这里不知道什么原因无法使用Validator类进行表单验证
         * 一旦使用Validator类进行表单验证就会报错Tenant could not be identified on domain
         * 只能在此处进行手动进行验证
        */
        if(empty($input['name'])){
            ReturnJson(FALSE,'站点名称不能为空');
        }
        if(empty($input['english_name'])){
            ReturnJson(FALSE,'英语名称不能为空');
        }
        if(Site::where('english_name',$input['english_name'])->first()){
            ReturnJson(FALSE,'英语名称已存在，请更换其他的');
        }
        if(empty($input['domain'])){
            ReturnJson(FALSE,'域名不能为空');
        }
        if(empty($input['country_id'])){
            ReturnJson(FALSE,'国家ID不能为空');
        }
        if(empty($input['db_host'])){
            ReturnJson(FALSE,'数据库端口不能为空');
        }
        if(empty($input['db_database'])){
            ReturnJson(FALSE,'数据库名不能为空');
        }
        if(empty($input['db_username'])){
            ReturnJson(FALSE,'数据库登陆名不能为空');
        }
        if(empty($input['db_password'])){
            ReturnJson(FALSE,'数据库密码不能为空');
        }
        if(!isset($request->is_create)){
            ReturnJson(FALSE,'is_create不能为空');
        }
        // 创建者ID
        $input['created_by'] = $request->user->id;
        // 是否生成数据库0不生成，1生成！生成数据库必须是MYSQL的ROOT账号，不是ROOT账号否则无法生成数据库
        $is_create = $request->is_create;
        // is_create不是入库的字段变量所以删除
        unset($request->is_create);

        // 开启事务
        DB::beginTransaction();
        try {
            // 入库site表
            $model = new Site();
            $res = $model->create($input);
            if(!$res){
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE,trans('lang.add_error'));
            }
            // 创建租户
            $Tenant = new TenantController();
            $res = $Tenant->initTenant($is_create,$input['english_name'],$input['domain'],$input['db_host'],$input['db_database'],$input['db_username'],$input['db_password'],$input['db_port']);
            if($res !== true){
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE,$res);
            }
            DB::commit();
            ReturnJson(TRUE,'新增成功1');
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 编辑一个站点
     * @param Request $request
     */
    public function update(Request $request)
    {

        $input = $request->all();
        /**
         * 这里不知道什么原因无法使用Validator类进行表单验证
         * 一旦使用Validator类进行表单验证就会报错Tenant could not be identified on domain
         * 只能在此处进行手动进行验证
        */
        if(empty($input['id'])){
            ReturnJson(FALSE,'站点ID不能为空');
        }
        if(empty($input['name'])){
            ReturnJson(FALSE,'站点名称不能为空');
        }
        if(empty($input['english_name'])){
            ReturnJson(FALSE,'英语名称不能为空');
        }
        if(empty($input['domain'])){
            ReturnJson(FALSE,'域名不能为空');
        }
        if(empty($input['country_id'])){
            ReturnJson(FALSE,'国家ID不能为空');
        }
        if(empty($input['db_host'])){
            ReturnJson(FALSE,'数据库端口不能为空');
        }
        if(empty($input['db_database'])){
            ReturnJson(FALSE,'数据库名不能为空');
        }
        if(empty($input['db_username'])){
            ReturnJson(FALSE,'数据库登陆名不能为空');
        }
        if(empty($input['db_password'])){
            ReturnJson(FALSE,'数据库密码不能为空');
        }
        if(!isset($request->is_create)){
            ReturnJson(FALSE,'is_create不能为空');
        }
        // 创建者ID
        $input['created_by'] = $request->user->id;

        // 开启事务
        DB::beginTransaction();
        try {
            // 入库site表
            $model = new Site();
            $model = $model->findOrFail($input['id']);
            $res = $model->update($input);
            if(!$res){
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE,trans('lang.update_error'));
            }
            // 创建租户
            $Tenant = new TenantController();
            $res = $Tenant->updateTenant($input['id'],$input['english_name'],$input['domain'],$input['db_host'],$input['db_database'],$input['db_username'],$input['db_password'],$input['db_port']);
            if($res !== true){
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE,$res);
            }
            DB::commit();
            ReturnJson(TRUE,trans('lang.update_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE,$e->getMessage());
        }
    }
    /**
     * 删除一个站点
     */
    public function destroy (Request $request)
    {
        DB::beginTransaction();
        try {
            if(empty($request->ids)){
                ReturnJson(FALSE,'请输入需要删除的ID');
            }
            $record = $this->ModelInstance()->query();
            $ids = $request->ids;
            if(!is_array($ids)){
                $ids = explode(",",$ids);
            }
            $record->whereIn('id',$ids);
            if(!$record->delete()){
                // 回滚事务
                DB::rollBack();
                ReturnJson(FALSE,trans('lang.delete_error'));
            }
            $Tenant = new TenantController();
            foreach ($ids as $id) {
                $res = $Tenant->destroyTenant($id);
                if($res !== true){
                    // 回滚事务
                    DB::rollBack();
                    ReturnJson(FALSE,$res);
                }
            }
            DB::commit();
            ReturnJson(TRUE,trans('lang.delete_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            ReturnJson(FALSE,$e->getMessage());
        }
    }

    /**
     * 查询列表页
     * @param $request 请求信息
     * @param int $page 页码
     * @param int $pageSize 页数
     * @param Array $where 查询条件数组 默认空数组
     */
    public function list (Request $request) {
        try {
            $model = $this->ModelInstance()->query();
            if(!empty($request->search)){
                $request->search = json_decode($request->search,TRUE);
                // 过滤条件数组，将空值的KEY过滤掉
                $search = array_filter($request->search,function($map){
                    if($map != ''){
                        return true;
                    }
                });
                // 使用Eloquent ORM来进行数据库查询
                foreach ($search as $field => $value) {
                    // 如果值是数组，则使用whereIn方法
                    if (is_array($value)) {
                        $model->whereIn($field, $value);
                    } else {
                        $model->where($field, $value);
                    }
                }
            }
            $is_super = Role::whereIn('id',explode(',',$request->user->role_id))->where('is_super',1)->count();

            if($is_super == 0){
                $roles = Role::whereIn('id',explode(',',$request->user->role_id))->pluck('site_id')->toArray();
                $site_ids = [];
                foreach ($roles as $role) {
                    if(!empty($role)){
                        $site_ids = array_merge($site_ids,$role);
                    }
                }
                $site_ids = array_unique($site_ids);
                $model->whereIn('id',$site_ids);
            }
            // 总数量
            $count = $model->count();
            // 总页数
            $pageCount = $request->pageSize > 0 ? ceil($count/$request->pageSize) : 1;
            // 当前页码数
            $page = $request->page ? $request->page : 1;
            $pageSize = $request->pageSize ? $request->pageSize : 100;

            // 查询偏移量
            if(!empty($request->page) && !empty($request->pageSize)){
                $model->offset(($request->page - 1) * $request->pageSize);
            }
            // 查询条数
            if(!empty($request->pageSize)){
                $model->limit($request->pageSize);
            }
            // 数据排序
            $order = $request->order ? $request->order : 'id';
            // 升序/降序
            $sort = (strtoupper($request->sort) == 'ASC') ? 'ASC' : 'DESC';

            $record = $model->orderBy($order,$sort)->get();
            $data = [
                'count' => $count,
                'pageCount' => $pageCount,
                'page' => $page,
                'pageSize' => $pageSize,
                'list' => $record
            ];
            ReturnJson(TRUE,trans('lang.request_success'),$data);
        } catch (\Exception $e) {
            ReturnJson(FALSE,$e->getMessage());
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
        exec("git pull",$res,$status);
        var_dump($res,$status);
    }

    /**
     * 推送消息到mq中
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveUpSite(Request $request){
        try{
            $className = get_class($this);

            $id = $request->input('id');

            if(!$id) ReturnJson(FALSE,'缺少站点id');

            $info = Site::where('id',$id)->select('id','english_name')->first()->toArray();

            $englishName = $info['english_name'];

            $data = json_encode(['class' => 'Modules\Admin\Http\Controllers\SiteController', 'method' => 'message', 'data'=>$info]);
            RabbitmqService::push('we123',$englishName,'we123','fanout' ,$data);

            ReturnJson(TRUE,'操作成功');
        }catch (\Exception $e){
            var_dump($e->getMessage());die;
            return $this->failed($e->getMessage().$e->getLine());
            ReturnJson(false,'操作失败');
        }
    }

    /**
     * 异常扑获
     * @param \Exception $exception
     */
    public function failed(\Exception $exception){
        print_r($exception->getMessage());
    }

    /**
     * 分站点接收队列更新指令
     * @param $params
     * @return void
     * @throws \Exception
     */
    public static function message($params = null)
    {
        if(empty($params)){
            echo ' 我没有参数1 ';
        }else{
            echo ' 我有参数2 ';
            $RootPath = base_path();
            var_dump($params);
            $RootPath = 'D:\phpstudy\phpstudy_pro\WWW\site\siteadmin.qyrdata.com';
            $exec = "cd ".$RootPath;
            $exec .= " & git pull";
            exec($exec,$res,$status);
            var_dump($res,$status);
            $result = [];
            $result['message'] = $res;
            $result['status'] = $status;
            $result['data'] = $params['data'];

            $data = json_encode(['class' => 'Modules\Admin\Http\Controllers\SiteController', 'method' => 'callbackResults', 'data'=>$result]);
            RabbitmqService::push('result','result','result','fanout' ,$data);
        }
    }

    /**
     * git更新完成返回总平台的回调
     * @param $params
     * @return void
     */
    public static function callbackResults($params = null)
    {
        //将数据入库数据表
        $res = SiteUpdateLog::insert(
            [
                'site_id' => $params['data']['id'],
                'english_name' => $params['data']['english_name'],
                'message'=>$params['message'][0],
                'status'=>$params['status'],
                'created_at'=>time(),
                'updated_at'=>time()],
        );
        if($res){
            echo '保存成功';
            //将结果加到缓存
            cache()->put($params['data']['english_name'], [
                'site'=>$params['data']['id'],
                'english_name'=>$params['data']['english_name'],
                'message'=>$params['message'][0],
                'status'=>$params['status'],
            ], 600);
        }else{
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
        if(empty($data)){
            ReturnJson(False,'未获取到信息');
        }
        ReturnJson(TRUE,trans('lang.request_success'),$data);
    }

}
