<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitmqConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq_consumer';//给消费者起个command名称

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
     * @return int
     */
    public function handle()
    {
        $this->subscribe();
    }

    /**
     * 发布订阅模式 -订阅
     * publish-and- subscribe， 即发布订阅模型。在Pub/Sub模型中，生产者将消息发布到一个主题(Topic)中，订阅了该Topic的所有下游消费者，都可以接收到这条消息。
     * Author: 李硕
     * Date: 2022/5/7
     * Time: 11:03
     */
    public function subscribe()
    {
        $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest','/');
        $channel = $connection->channel();

        $channel->exchange_declare('niuniu1232', 'fanout', false, true, false);

        list($queue_name, ,) = $channel->queue_declare("niuniu1232", false, true, false, false);

        $channel->queue_bind($queue_name, 'niuniu1232');

        echo " [*] Waiting for logs. To exit press CTRL+C\n";

//        $callback = function ($msg) {
//            echo ' 我是订阅者开始进行消费[x] ', $msg->body, "\n";
//        };
        //跳转到对应的方法
        // 定义回调函数，处理接收到的消息
        $callback = function (AMQPMessage $message) {
            // 解析消息内容
            $data = json_decode($message->body, true);
            $class = $data['class'];
            $method = $data['method'];

            // 根据类名和方法名调用相应的类和方法
            $instance = new $class();
            call_user_func([$instance, $method]);
        };

        // 订阅队列并处理消息
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        //跳转到对应的方法end
//        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
