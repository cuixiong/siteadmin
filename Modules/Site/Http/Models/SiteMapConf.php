<?php
/**
 * SiteMapConf.php UTF-8
 * 站点地图配置文件
 *
 * @date    : 2025/4/22 9:16 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Models;
class SiteMapConf extends Base {
    protected $table = 'site_map_conf';
    // 设置允许入库字段,数组形式
    protected $fillable
        = ['id', 'name', 'code', 'loc', 'xml_name', 'sort', 'status'];


}
