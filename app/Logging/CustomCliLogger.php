<?php
/**
 * CustomCliLogger.php UTF-8
 * 自定义cli日志
 *
 * @date    : 2024/6/27 11:07 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */
namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CustomCliLogger
{
    /**
     *
     * @param array $config
     *
     * @return Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('cli');

        $date = date('Y-m-d');
        $path = storage_path("logs/laravel-{$date}-cli.log");

        $handler = new StreamHandler($path, Logger::DEBUG);

        // 设置自定义日期格式的日志格式化器
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        return $logger;
    }
}

