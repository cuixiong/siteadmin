<?php
namespace App\Console\Commands;

class TestCommand extends RabbitmqConnectCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Test';
    protected $ExchangeName = 'test'; // exchange name
    protected $QueueName = 'test'; // queue name
    protected $Model = 'fanout';
}