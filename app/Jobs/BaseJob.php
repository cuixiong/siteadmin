<?php
/**
 * BaseJob.php UTF-8
 * 基类
 *
 * @date    : 2024/5/21 14:30 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Jobs;
trait BaseJob {
    public function callFuncBack($dataJsonStr) {
        $data = json_decode($dataJsonStr, true);
        $class = $data['class'];
        $method = $data['method'];
        $instance = new $class();
        call_user_func([$instance, $method],$data);
    }
}
