<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Site\Http\Controllers\SyncThirdProductController;

class SyncDataCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:syncdata {--site=}';

    public function handle() {
        //设置日志
        config(['logging.default' => 'cli']);
        Log::error("开始同步数据:");
        $option = $this->option();
        $syncThirdProduct = new SyncThirdProductController();
        if(!empty($option['site'] )){
            $site = $option['site'];
            $syncThirdProduct->site = $site;
        }else{
            echo "参数异常".PHP_EOL;die;
        }
        echo "开始同步数据".PHP_EOL;
        try{
            $syncThirdProduct->handlerSyncDataJob();
        }catch (\Exception $e){
            Log::error('同步数据异常,错误信息:'.$e->getMessage());
            echo "同步数据异常: {$e->getMessage()}".PHP_EOL;
        }

        echo "开始同步完成".PHP_EOL;
    }
}
