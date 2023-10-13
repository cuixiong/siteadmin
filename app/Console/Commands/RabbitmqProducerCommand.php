<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

//引入amqp扩展
use Modules\Admin\Http\Controllers\SiteController;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitmqProducerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq_producer';//给生产者起个command名称

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *  生产者消息代码
     * @return int
     */
    public function handle()
    {

        $this->publish();
    }

    /**
     * 发布订阅模式的发布操作
     * publish-and- subscribe， 即发布订阅模型。在Pub/Sub模型中，生产者将消息发布到一个主题(Topic)中，订阅了该Topic的所有下游消费者，都可以接收到这条消息。
     * Author: 李硕
     * Date: 2022/5/7
     * Time: 11:03
     */
    public function publish()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        //发布模式(fanout)
        $channel->exchange_declare('niuniu12322', 'fanout', false, false, false);
        $argv = [];
        $data = implode(' ', array_slice($argv, 1));
        //跳转到对应的方法测试
        $msg = new AMQPMessage(json_encode(['class' => 'Modules\Admin\Http\Controllers\SiteController', 'method' => 'message']), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT]);
        new SiteController();
        //跳转到对应的方法测试end
//        if (empty($data)) {
//            $data = "info: 我是发布者生产的消息消息!";
//        }
//        $msg = new AMQPMessage($data);

        $channel->basic_publish($msg, 'niuniu12322');

        echo ' [x] Sent ', $data, "\n";

        $channel->close();
        $connection->close();
    }
}
