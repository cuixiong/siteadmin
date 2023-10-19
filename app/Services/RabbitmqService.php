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

    /**
     * RabbitMQ 生产者
     * @param $queue 队列名称
     * @param string $exchange 交换机名称
     * @param string $routing_key 设置路由
     * @param string $type 队列类型
     * @param $messageBody 消息体
     * @throws \Exception
     */
    public static function push($queue,$exchange,$routing_key,$type='direct',$messageBody){
        // 建立连接
        $connection = self::getConnect();
        //构建通道（mq的数据存储与获取是通过通道进行数据传输的）
        $channel = $connection->channel();
        //监听数据,成功
        $channel->set_ack_handler(function (AMQPMessage $message){
            dump("数据写入成功");
        });
        //监听数据,失败
        $channel->set_nack_handler(function (AMQPMessage $message){
            dump("数据写入失败");
        });

        //声明一个队列
        $channel->queue_declare($queue,false,true,false,false);

        //指定交换机，若是路由的名称不匹配不会把数据放入队列中
        $channel->exchange_declare($exchange,$type,false,true,false);

        //队列和交换器绑定/绑定队列和类型
        $channel->queue_bind($queue,$exchange,$routing_key);

        $config = [
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ];

        //实例化消息推送类
        $message = new AMQPMessage($messageBody,$config);

        //消息推送到路由名称为$exchange的队列当中
        $channel->basic_publish($message,$exchange,$routing_key);

        //监听写入
        $channel->wait_for_pending_acks();

        //关闭消息推送资源
        $channel->close();

        //关闭mq资源
        $connection->close();
    }
}

