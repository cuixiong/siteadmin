<?php
namespace App\Console\Commands;
use App\Console\Commands\RabbitmqConsumerCommand;
class RabbitmqConsumerSiteCommand extends RabbitmqConsumerCommand
{
    public $Exchange = '168report';
    public $Queue = '168report';
    public $QueueBind = '168report';
}