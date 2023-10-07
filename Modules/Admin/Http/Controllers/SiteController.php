<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\Site;

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
                ReturnJson(FALSE,'新增失败');
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
                ReturnJson(FALSE,'更新失败');
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
            ReturnJson(TRUE,'更新成功');
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
                ReturnJson(FALSE,'删除失败');
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
            ReturnJson(TRUE,'删除成功');
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
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
}
