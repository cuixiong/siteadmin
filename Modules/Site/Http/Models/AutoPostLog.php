<?php
/**
 * AutoPostLog.php UTF-8
 * 自动发帖日志
 *
 * @date    : 2025/3/24 10:13 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Models;
class AutoPostLog extends Base {
    const POST_STATUS_SUCCESS = 1; // 成功
    const POST_STATUS_EXIST   = 2; // 已存在
    const POST_STATUS_INGORE  = 3;   // 忽略
    const POST_STATUS_ERROR   = 4; // 错误

    public static $postStatus = [
        self::POST_STATUS_SUCCESS => '成功',
        self::POST_STATUS_EXIST   => '已存在',
        self::POST_STATUS_INGORE  => '忽略',
        self::POST_STATUS_ERROR   => '错误',
    ];

    protected $table = 'auto_post_log';
    // 设置允许入库字段,数组形式
    protected $fillable
        = ['id', 'code', 'product_id', 'wp_link', 'title_template_id', 'content_template_id', 'created_at',
           'post_status', 'detail'];
}
