<?php
namespace App\Console\Commands;

class ProductUploadFileCommand extends RabbitmqConnectCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProductUploadFile';
    protected $ExchangeName = 'Products'; // exchange name
    protected $QueueName = 'products-file-queue'; // queue name
    protected $Model = 'direct';

    protected function initChannel()
    {
        if(!$this->channel){
            // channel
            $this->channel = $this->connection->channel();
            // 
            $this->channel->queue_declare($this->QueueName,false,true,false,false);
            //
            $this->channel->exchange_declare($this->ExchangeName,$this->Model,false,true,false);
            //
            $this->channel->queue_bind($this->QueueName,$this->ExchangeName,'productsKey1');
        }
    }
}