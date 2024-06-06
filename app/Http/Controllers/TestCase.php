<?php
/**
 * TestCase.php UTF-8
 * 测试用例
 *
 * @date    : 2024/6/6 14:26 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */
namespace App\Http\Controllers;

class TestCase extends Controller {
    public function test1() {
        $a = (new SendEmailController())->register(167);
        var_dump($a);die;
    }

}
