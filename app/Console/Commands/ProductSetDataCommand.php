<?php
/**
 * ProductSetDataCommand.php UTF-8
 * 游戏设置数据命令行
 *
 * @date    : 2024/6/20 11:27 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Site\Http\Controllers\SiteCrontabController;

class ProductSetDataCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:handlerProductData {--site=}';

    public function handle() {
        $option = $this->option();
        $siteCrontab = new SiteCrontabController();
        $siteCrontab->isCli = true;

        if (!empty($option['site'])) {
            $site = $option['site'];
            $siteCrontab->site = $site;
        } else {
            echo "参数异常".PHP_EOL;
            die;
        }
        echo "开始处理报告数据".PHP_EOL;
        try {
            $siteCrontab->handlerProductStatus();
        } catch (\Exception $e) {
            echo "处理报告数据异常{$e->getMessage()}".PHP_EOL;
            \Log::error('处理报告数据异常--错误信息与数据:'.json_encode([$e]));
        }
        echo "处理报告数据完成".PHP_EOL;
    }
}
