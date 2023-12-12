<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TimedTaskCommand extends Command
{
    protected $ExchangeName; // exchange name
    protected $QueueName = 'timed_task'; // queue name
    protected $channel;// RabbitMQ channel
    protected $connection;// RabbitMQ connection
    protected $Model;// RabbitMQ queue Model

    // connect config 
    protected $host;
    protected $port;
    protected $user;
    protected $pass;
    protected $vhost;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TimedTask';

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
    protected function connect(){
        $this->host = env('RABBITMQ_HOST');
        $this->port = env('RABBITMQ_PORT');
        $this->user = env('RABBITMQ_USER');
        $this->pass = env('RABBITMQ_PASSWORD');
        $this->vhost = $this->vhost ? $this->vhost : env('RABBITMQ_VHOST');

        $this->connection =  new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->pass,
            $this->vhost
        );
        return $this->connection;
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
     * set Queque Name
     */
    protected function SetQueueName($QueueName = '')
    {
        $this->QueueName = $QueueName;
    }

    /**
     * set Exchange Name
     */
    protected function SetExchangeName($ExchangeName = '')
    {
        $this->ExchangeName = $ExchangeName;
    }

    /**
     * set vhost name
     */
    protected function SetVhostName($vhost = '')
    {
        $this->vhost = $vhost;
    }
    /**
     * set channel
     */
    protected function initChannel()
    {
        if(!$this->channel){
            // channel
            $this->channel = $this->connection->channel();
        }
    }

    /**
     * Close the connection
     */
    protected function close()
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * Call back function
     */
    protected function CallFuncBack()
    {
        return function (AMQPMessage $message) {
            $data = json_decode($message->body, true);
            $class = $data['class'];
            $method = $data['method'];
            $instance = new $class();
            call_user_func([$instance, $method],$data);
        };
    }

    /**
     * Subscribe
     */
    public function subscribe()
    {
        $this->connect(); // Establishing a connection
        $this->initChannel();// initialization channel
        $callback = $this->CallFuncBack();
        $this->channel->basic_consume($this->QueueName, '', false, true, false, false, $callback);
        while (true) {
            $this->channel->wait();
        }
        $this->close();
    }
}
