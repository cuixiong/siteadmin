<?php
namespace App\Console\Commands;
use App\Console\Commands\RabbitmqConsumerCommand;
class RabbitmqConsumerSiteCommand extends RabbitmqConsumerCommand
{
    // 消费者command名称
    protected $signature = 'rabbitmq_consumer_site';
    public $Exchange = 'test';
    public $Queue = 'test';
    public $QueueBind = 'test';
}