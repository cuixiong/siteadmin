<?php
/**
 * QueueConst.php UTF-8
 * 队列常量
 *
 * @date    : 2024/5/21 14:00 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Const;
class QueueConst {
    // php artisan queue:work --queue=export_product
    const QUEEU_EXPORT_PRODUCT = 'export_product';  // 导出报告队列
    // php artisan queue:work --queue=handler_excel
    const QUEEU_HANDLER_EXCEL = 'handler_excel';  // 处理excel队列
    // php artisan queue:work --queue=time_task
    const QUEEU_TIME_TASK = 'time_task';  // TimeTask
    // php artisan queue:work --queue=upload_product
    const QUEEU_UPLOAD_PRODUCT = 'upload_product';  // UploadProduct 读取上传报告excel数据 队列
    // php artisan queue:work --queue=handler_product_excel
    const QUEEU_HANDLER_PRODUCT_EXCEL = 'handler_product_excel';  // HandlerProductExcel 处理上传报告excel数据
    // php artisan queue:work --queue=operation_log
    const QUEEU_OPERATION_LOG = 'operation_log';  // 操作日志
    // php artisan queue:work --queue=sync_spginx_index
    const SYNC_SPGINX_INDEX = 'sync_spginx_index';  // 同步sphinx索引

    // php artisan queue:work --queue=export_view_goods
    const QUEEU_EXPORT_VIEW_GOODS = 'export_view_goods';  // 导出浏览记录队列
}
