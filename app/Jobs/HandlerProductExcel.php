<?php
/**
 * HandlerProductExcel.php UTF-8
 * 处理报告的excel
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

class HandlerProductExcel implements ShouldQueue {
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
        echo "开始".PHP_EOL;
        try {
            $this->callFuncBack($this->data);
        } catch (\Exception $e) {
            $errData = [
                'data'  => $this->data,
                'error' => $e->getMessage(),
            ];
            \Log::error('处理报告的excel--错误信息与数据:'.json_encode($errData));
        }

        return true;
    }
}
