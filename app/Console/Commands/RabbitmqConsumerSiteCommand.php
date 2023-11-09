<?php
namespace App\Console\Commands;
use App\Console\Commands\RabbitmqConsumerCommand;
class RabbitmqConsumerSiteCommand extends RabbitmqConsumerCommand
{
    protected $signature = 'rabbitmq_consumer_site';
    public $Exchange = 'test_qq';
    public $Queue = 'test_queue01';
    public $QueueBind = 'test_qq';
}