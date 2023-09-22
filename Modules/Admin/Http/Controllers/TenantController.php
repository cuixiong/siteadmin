<?php

namespace Modules\Admin\Http\Controllers;
use Illuminate\Routing\Controller;
use App\Models\Tenant;
class TenantController extends Controller
{
    /**
     * 初始化一个租户
     */
    public function initTenant($id,$domian,$DB_Host,$DB_DATABASE,$DB_USERNAME,$DB_PASSWORD,$DB_PORT)
    {
        // 创建租户
        $tenant = Tenant::create([
            'id' => $id,
            'tenancy_db_host' => $DB_Host,
            'tenancy_db_name' => $DB_DATABASE,
            'tenancy_db_username' => $DB_USERNAME,
            'tenancy_db_password' => $DB_PASSWORD,
            'tenancy_db_port' => $DB_PORT,
        ]);
        $tenant->domains()->create([
            'domain' => $domian,
        ]);
        // 保存租户配置
        $tenant->save();
        return true;
    }
}
