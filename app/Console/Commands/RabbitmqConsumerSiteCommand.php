<?php
namespace App\Console\Commands;
use App\Console\Commands\RabbitmqConsumerCommand;
class RabbitmqConsumerSiteCommand extends RabbitmqConsumerCommand
{
    protected $signature = 'rabbitmq_consumer_site';
    public $Exchange = 'test';
    public $Queue = 'test';
    public $QueueBind = 'test';
}