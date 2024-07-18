<?php

namespace Modules\Site\Http\Models;
class IpBanLog extends Base {
    protected $table = 'ip_ban_log';
    // 设置允许入库字段,数组形式
    protected $fillable = ['id', 'ip', 'ip_addr', 'route', 'sort', 'status'];
}
