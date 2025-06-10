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
use Modules\Site\Http\Models\PostSubjectStrategy;

class ExecutePostSubjectStrategyCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ExecutePostSubjectStrategy:execute {--site=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'execute post subject strategy command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        //设置日志
        config(['logging.default' => 'cli']);
        $option = $this->option();
        $siteName = $option['site'];
        if (empty($siteName)) {
            echo '参数异常' . PHP_EOL;
            exit;
        }
        tenancy()->initialize($siteName);
        
        $configs = PostSubjectStrategy::query()->andWhere(['status' => 1,])->get()->toArray();
        foreach ($configs as $config) {
            $result = (new PostSubjectStrategy())->assignStrategy(2, $config);
            echo $result['msg'];
            echo "\r\n";
        }

    }
}
