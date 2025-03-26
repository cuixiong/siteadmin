<?php
/**
 * AutoPostCommand.php UTF-8
 * 自动发帖命令行
 *
 * @date    : 2025/3/24 15:52 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Site\Http\Controllers\AutoPostController;
use Modules\Site\Http\Controllers\SyncThirdProductController;

class AutoPostCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:post {--site=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto post command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        //设置日志
        config(['logging.default' => 'cli']);
        $option = $this->option();
        $autoPostController = new AutoPostController();
        if(!empty($option['site'] )){
            $site = $option['site'];
            $autoPostController->site = $site;
        }else{
            echo "参数异常".PHP_EOL;die;
        }
        echo "开始自动发帖".PHP_EOL;
        try{
            $autoPostController->handlerAutoPostJob();
        }catch (\Exception $e){
            Log::error('自动发帖异常,错误信息:'.$e->getMessage());
            echo "自动发帖异常: {$e->getMessage()}".PHP_EOL;die;
        }

        echo "自动发帖完成".PHP_EOL;
    }
}
