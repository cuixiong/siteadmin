<?php
/**
 * XsSyncIndexCommand.php UTF-8
 * 讯搜同步索引
 *
 * @date    : 2024/5/23 11:36 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace App\Console\Commands;
class XsSyncIndexCommand extends RabbitmqConnectCommand {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature    = 'xssyncindex';
    protected $ExchangeName = 'Products'; // exchange name
    protected $QueueName    = 'xssyncindex-queue'; // queue name
    protected $Model        = 'direct';

    protected function initChannel() {
        if (!$this->channel) {
            // channel
            $this->channel = $this->connection->channel();
            //
            $this->channel->queue_declare($this->QueueName, false, true, false, false);
            //
            $this->channel->exchange_declare($this->ExchangeName, $this->Model, false, true, false);
            //
            $this->channel->queue_bind($this->QueueName, $this->ExchangeName, 'productsKey1');
        }
    }
}
