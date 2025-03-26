<?php
/**
 * AutoPostConfig.php UTF-8
 * 自动发帖配置
 *
 * @date    : 2025/3/21 14:21 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Models;
class AutoPostConfig extends Base {
    protected $table = 'auto_post_config';
    // 设置允许入库字段,数组形式
    protected $fillable
        = ['id', 'name', 'code', 'title_template_ids', 'content_template_ids', 'product_category_ids',
           'start_product_id', 'post_num', 'db_host', 'db_name', 'db_username', 'db_password', 'db_charset', 'domain',
           'created_by', 'created_at', 'updated_by', 'updated_at', 'sort', 'status'];
}
