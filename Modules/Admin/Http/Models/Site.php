<?php

namespace Modules\Admin\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\SyncMaster;
use Stancl\Tenancy\Database\Concerns\ResourceSyncing;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
class Site extends Model
{
    // 注意，我们在这个模型上强制使用中央连接
    use ResourceSyncing, CentralConnection;
    public $table='site';//这样寻找的就是没s的表
}