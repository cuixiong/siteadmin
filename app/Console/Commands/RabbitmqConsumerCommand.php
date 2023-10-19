<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitmqConsumerCommand extends Command
{
    public $Exchange = '';
    public $Queue = '';
    public $QueueBind = '';
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
     * 配置连接信息
     * @param use PhpAmqpLib\Connection\AMQPStreamConnection; 
     */
    private static function getConnect(){
        $Config = [
            'host' => env('RABBITMQ_HOST'),
            'port' => env('RABBITMQ_PORT'),
            'user' => env('RABBITMQ_USER'),
            'password' => env('RABBITMQ_PASSWORD'),
            'vhost' => '/',
        ];
        return new AMQPStreamConnection(
            $Config['host'],
            $Config['port'],
            $Config['user'],
            $Config['password'],
            $Config['vhost']
        );
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
     * publish-and- subscribe， 即发布订阅模型。
     * 在Pub/Sub模型中，生产者将消息发布到一个主题(Topic)中
     * 订阅了该Topic的所有下游消费者，都可以接收到这条消息。
     */
    public function subscribe()
    {
        // 建立连接
        $connection = self::getConnect();
        //构建通道（mq的数据存储与获取是通过通道进行数据传输的）
        $channel = $connection->channel();
        $channel->exchange_declare($this->Exchange, 'fanout', false, true, false);
        list($queue_name, ,) = $channel->queue_declare($this->Queue, false, true, false, false);
        $channel->queue_bind($queue_name, $this->QueueBind);
        // 定义回调函数，处理接收到的消息
        $callback = function (AMQPMessage $message) {
            // 解析消息内容
            $data = json_decode($message->body, true);
            $class = $data['class'];
            $method = $data['method'];
            // 根据类名和方法名调用相应的类和方法
            $instance = new $class();
            call_user_func([$instance, $method],$data);
        };
        // 订阅队列并处理消息
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
        while ($channel->is_open()) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }
}
