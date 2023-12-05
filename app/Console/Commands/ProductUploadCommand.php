<?php
namespace App\Console\Commands;

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
    protected $Model = 'fanout';
}