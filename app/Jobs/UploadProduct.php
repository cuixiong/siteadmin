<?php
/**
 * UploadProduct.php UTF-8
 * 上传报告队列处理
 *
 * @date    : 2024/5/21 13:21 下午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadProduct implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, BaseJob;

    public $data = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $start_time = microtime(true);
        echo "开始{$start_time}".PHP_EOL;
        try {
            $this->callFuncBack($this->data);
        } catch (\Exception $e) {
            $errData = [
                'data'  => $this->data,
                'error' => $e->getMessage(),
            ];
            \Log::error('处理上传excel报告数据失败--错误信息与数据:'.json_encode($errData));
        }
        $end_time = microtime(true);
        echo "结束{$end_time}".PHP_EOL;
        return true;
    }
}
