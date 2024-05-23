<?php
namespace App\Console\Commands;

use PhpAmqpLib\Message\AMQPMessage;

class ProductUploadCommand extends RabbitmqConnectCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProductUpload';
    protected $ExchangeName = 'Products'; // exchange name
    protected $QueueName = 'products-queue'; // queue name
    protected $Model = 'direct';

    protected function initChannel()
    {
        if(!$this->channel){
            // channel
            $this->channel = $this->connection->channel();
            // 设置 exclusive 为 true  倒数第二个参数 为 true
            $this->channel->queue_declare($this->QueueName,false,true,true,false);
            //
            $this->channel->exchange_declare($this->ExchangeName,$this->Model,false,true,false);
            //
            $this->channel->queue_bind($this->QueueName,$this->ExchangeName,'productsKey2');
            //设置预取数量
            $this->channel->basic_qos(null, 1, null);
        }
    }

    /**
     * Subscribe
     */
    public function subscribe()
    {
        $this->connect(); // Establishing a connection
        $this->initChannel();// initialization channel
        $callback = $this->CallFuncBack();
        $this->channel->basic_consume($this->QueueName, '', false, false, false, false, $callback);
        while (true) {
            $this->channel->wait();
        }
        $this->close();
    }

    /**
     * Call back function
     */
    protected function CallFuncBack()
    {
        return function ($message) {
            $data = json_decode($message->body, true);
            $class = $data['class'];
            $method = $data['method'];
            $instance = new $class();
            call_user_func([$instance, $method],$data);
            $message->ack();
        };
    }

}
