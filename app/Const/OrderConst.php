<?php
/**
 * PayConst.php UTF-8
 * 支付常量
 *
 * @date    : 2024/7/31 11:17 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Const;
class OrderConst {
    const PAY_UNPAID  = 1;
    const PAY_SUCCESS = 2;
    const PAY_CANCEL  = 3;
    const PAY_FINISH  = 4;
    const PAY_FAILED  = 5;
    public static $PAY_STATUS_TYPE
        = [
            self::PAY_UNPAID  => '未支付',
            self::PAY_SUCCESS => '已支付',
            self::PAY_CANCEL  => '已取消',
            self::PAY_FINISH  => '已完成',
            self::PAY_FAILED  => '支付失败',
        ];
}
