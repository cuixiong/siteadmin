<?php
namespace App\Services;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitmqService.
 */
class RabbitmqService
{
    const READ_LINE_NUMBER = 0;
    const READ_LENGTH      = 1;
    const READ_DATA        = 2;

    public $config;

    public static $prefix   = 'laravel'; // 默认队列前缀
    protected $exchangeName = 'laravel'; // 默认交换机名称
    protected $queueName    = 'laravel'; // 默认队列名称
    protected $queueMode    = ''; // 默认队列模式
    protected $routingKey    = ''; // direct模式绑定key

    /**
     * @var \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected $connection;
    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;
    protected $queue;
	
    //配置项
    private $host;
    private $port;
    private $user;
    private $pass;
    private $vhost;

    public function __construct($config = [])
    {
        //$this->config = $config;

        //设置rabbitmq配置值
        $this->host  = env('RABBITMQ_HOST');
        $this->port  = env('RABBITMQ_PORT');
        $this->user  = env('RABBITMQ_USER');
        $this->pass  = env('RABBITMQ_PASSWORD');
        $this->vhost = env('RABBITMQ_VHOST');

        $this->connect();
        $this->initChannel();
    }

    public function __call($method, $args = [])
    {
        $reConnect = false;
        try {
            $this->initChannel();
            $result = call_user_func_array([$this->channel, $method], $args);
        } catch (\Exception $e) {
            //已重连过，仍然报错
            if ($reConnect) {
                throw $e;
            }
            if ($this->connection) {
                $this->close();
            }
            $this->connect();
            $reConnect = true;
        }
        return $result;
    }

    /**
     * 连接rabbitmq消息队列.
     *
     * @return bool
     */
    public function connect()
    {
        try {
            if ($this->connection) {
                unset($this->connection);
            }
            $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pass, $this->vhost);
        } catch (\Exception $e) {
			echo __CLASS__ ."Swoole RabbitMQ Exception'".$e->getMessage();
            return false;
        }
    }

    /**
     * 关闭连接.
     */
    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * 设置交换机名称.
     *
     * @param string $exchangeName
     */
    public function setExchangeName($exchangeName = '')
    {
        $exchangeName && $this->exchangeName = $exchangeName;
    }

    /**
     * 设置队列名称.
     *
     * @param string $queueName
     */
    public function setQueueName($queueName = '')
    {
        $queueName && $this->queueName = $queueName;
    }

    /**
     * 设置队列模式
     * 
     * @param string $mode
     */
    public function setQueueMode($mode = '')
    {
        $mode && $this->queueMode = $mode;
    }
    
    /**
     * 设置routing_key
     * 
     * @param string $mode
     */
    public function setRoutingKey($routingKey = '')
    {
        $routingKey && $this->routingKey = $routingKey;
    }

    /**
     * 设置频道.
     */
    public function initChannel()
    {
        if (!$this->channel) {
            //通道
            $this->channel = $this->connection->channel();
            // $this->channel->queue_declare($this->queueName, false, true, false, false);
            // $this->channel->exchange_declare($this->exchangeName, $this->queueMode, false, true, false);
            // $this->channel->queue_bind($this->queueName, $this->exchangeName);
        }
    }

    /**
     * 插入队列数据.
     *
     * @param $data
     * @return bool
     */
    public function push($data)
    {	
        $this->connect();
        $this->initChannel();
        $message = new AMQPMessage($data, ['content_type'=>'text/plain', 'devlivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->channel->basic_publish($message, $this->exchangeName, $this->routingKey);
        $this->channel->wait_for_pending_acks();
        $this->close();
    }

    public function SimpleModePush($controller, $method, $data)
    {
        $data = json_encode(['class' => $controller,'method' => $method, 'data' => $data]);
        $this->channel->queue_declare($this->queueName, false, false, false, false);
        $message = new AMQPMessage($data, ['content_type'=>'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->channel->basic_publish($message, '',$this->queueName);
        $this->channel->wait_for_pending_acks();
    }
}