<?php

namespace Modules\Admin\Http\Models;
class SiteNginxConf extends Base {
    // 设置允许入库字段,数组形式
    protected $fillable = ['conf_temp_path', 'conf_real_path', 'status', 'updated_by', 'created_by'];
    protected $table = 'site_nginx_conf';
    protected $primaryKey = 'id';

}
