<?php
namespace App\Services;

use Modules\Admin\Http\Models\Site;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitmqService
{
    /**
     * 配置连接信息
     * @param use PhpAmqpLib\Connection\AMQPStreamConnection;
     */
    private static function getConnect(){
        $Config = [
            'host' => env('RABBITMQ_HOST'),
            'port' => env('RABBITMQ_PORT'),
            'user' => env('RABBITMQ_USER'),
            'password' => env('RABBITMQ_PASSWORD'),
            // 'vhost' => env('RABBITMQ_VHOST'),
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


    public static function push($queue,$exchange,$routing_key,$type='direct',$messageBody){
        $connection = self::getConnect();
        $channel = $connection->channel();
        $channel->set_ack_handler(function (AMQPMessage $message){
            dump("数据写入成功");
        });
        $channel->set_nack_handler(function (AMQPMessage $message){
            dump("数据写入失败");
        });
        $channel->queue_declare($queue,false,true,false,false);
        $channel->exchange_declare($exchange,$type,false,true,false);
        $channel->queue_bind($queue,$exchange,$routing_key);

        $config = [
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ];
        $message = new AMQPMessage($messageBody,$config);
        $channel->basic_publish($message,$exchange,$routing_key);
        $channel->wait_for_pending_acks();
        $channel->close();
        $connection->close();
    }
}

