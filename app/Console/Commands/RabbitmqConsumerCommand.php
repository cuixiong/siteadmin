<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitmqConsumerCommand extends Command
{
    public $Exchange = '168report';
    public $Queue = '168report';
    public $QueueBind = '168report';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq_consumer';

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
     * @param use PhpAmqpLib\Connection\AMQPStreamConnection;
     */
    private static function getConnect(){
        $Config = [
            'host' => env('RABBITMQ_HOST'),
            'port' => env('RABBITMQ_PORT'),
            'user' => env('RABBITMQ_USER'),
            'password' => env('RABBITMQ_PASSWORD'),
            // 'vhost' => '/',
            'vhost' => '/test',
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
     */
    public function subscribe()
    {
        $connection = self::getConnect();
        $channel = $connection->channel();
        $channel->exchange_declare($this->Exchange, 'fanout', false, true, false);
        list($queue_name, ,) = $channel->queue_declare($this->Queue, false, true, false, false);
        $channel->queue_bind($queue_name, $this->QueueBind);
        $callback = function (AMQPMessage $message) {
            $data = json_decode($message->body, true);
            $class = $data['class'];
            $method = $data['method'];
            $instance = new $class();
            call_user_func([$instance, $method],$data);
        };
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
        while (1) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }

}
