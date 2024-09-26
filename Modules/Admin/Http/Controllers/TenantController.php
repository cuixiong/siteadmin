<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    /**
     * 初始化一个租户
     * @param bool $is_create 是否需要创建数据库
     * @param string $name 租户的英文标识
     * @param string $domain 租户域名
     * @param string $DB_HOST 数据库HOST
     * @param string $DB_DATABASE 数据库DATABASE
     * @param string $DB_USERNAME 数据库用户名
     * @param string $DB_PASSWORD 数据库密码
     * @param string $DB_PORT 数据库端口
     */
    public function initTenant($is_create, $name, $domain, $DB_HOST, $DB_DATABASE, $DB_USERNAME, $DB_PASSWORD, $DB_PORT)
    {
        try {
            if ($is_create == 1) {
                // 创建租户
                $tenant = Tenant::create([
                    'id' => $name,
                    'tenancy_db_host' => $DB_HOST,
                    'tenancy_db_name' => $DB_DATABASE,
                    'tenancy_db_username' => $DB_USERNAME,
                    'tenancy_db_password' => $DB_PASSWORD,
                    'tenancy_db_port' => $DB_PORT,
                ]);
                $tenant->domains()->create([
                    'domain' => $domain,
                ]);
                // 保存租户配置
                $tenant->save();
            } else {
                $time = date('Y-m-d H:i:s', time());
                $data = [
                    'created_at' => $time,
                    'updated_at' => $time,
                    'tenancy_db_host' => $DB_HOST,
                    'tenancy_db_name' => $DB_DATABASE,
                    'tenancy_db_port' => $DB_PORT,
                    'tenancy_db_password' => $DB_PASSWORD,
                    'tenancy_db_username' => $DB_USERNAME,
                ];
                $data = json_encode($data);
                // 入库tenants表
                DB::table('tenants')->insert(['id' => $name, 'created_at' => $time, 'updated_at' => $time, 'data' => $data]);
                // 入库domains
                DB::table('domains')->insert(['domain' => $domain, 'tenant_id' => $name, 'created_at' => $time, 'updated_at' => $time]);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 更新租户信息
     * @param string $id 租户ID
     * @param string $name 租户的英文标识
     * @param string $domain 租户域名
     * @param string $DB_HOST 数据库HOST
     * @param string $DB_DATABASE 数据库DATABASE
     * @param string $DB_USERNAME 数据库用户名
     * @param string $DB_PASSWORD 数据库密码
     * @param string $DB_PORT 数据库端口
     */
    public function updateTenant($oldName, $name, $domain, $DB_HOST, $DB_DATABASE, $DB_USERNAME, $DB_PASSWORD, $DB_PORT)
    {
        try {
            $time = date('Y-m-d H:i:s', time());
            $data = [
                'created_at' => $time,
                'updated_at' => $time,
                'tenancy_db_host' => $DB_HOST,
                'tenancy_db_name' => $DB_DATABASE,
                'tenancy_db_port' => $DB_PORT,
                'tenancy_db_password' => $DB_PASSWORD,
                'tenancy_db_username' => $DB_USERNAME,
            ];
            $data = json_encode($data);
            // 查询当前的租户信息
            $domains = DB::table('domains')->where('tenant_id', $oldName)->first();
            if ($domains) {

                // 入库tenants表
                $tenants = DB::table('tenants')->where('id', $domains->tenant_id)->first();
                if($tenants){
                    DB::table('tenants')->where('id', $domains->tenant_id)->update(['id' => $name, 'created_at' => $time, 'updated_at' => $time, 'data' => $data]);
                }else{
                    DB::table('tenants')->insert(['id' => $name, 'created_at' => $time, 'updated_at' => $time, 'data' => $data]);
                }

                // 入库domains
                DB::table('domains')->where('tenant_id', $domains->tenant_id)->update(['domain' => $domain, 'tenant_id' => $name, 'created_at' => $time, 'updated_at' => $time]);

            } else {
                // 入库tenants表
                $tenants = DB::table('tenants')->where('id', $name)->first();
                if($tenants){
                    DB::table('tenants')->where('id', $name)->update(['id' => $name, 'created_at' => $time, 'updated_at' => $time, 'data' => $data]);
                }else{
                    DB::table('tenants')->insert(['id' => $name, 'created_at' => $time, 'updated_at' => $time, 'data' => $data]);
                }
                
                // 入库domains
                DB::table('domains')->insert(['domain' => $domain, 'tenant_id' => $name, 'created_at' => $time, 'updated_at' => $time]);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 删除一个商户
     * @param string $id 租户ID
     */

    public function destroyTenant($id)
    {
        try {
            // 查询当前的租户信息
            $tenant = DB::table('domains')->where('id', $id)->first();
            //删除tenants表
            DB::table('tenants')->where('id', $tenant->tenant_id)->delete();
            //删除domains表
            DB::table('domains')->where('id', $id)->delete();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
