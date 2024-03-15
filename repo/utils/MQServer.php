<?php

namespace Utils;

use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * rabbitmq 模块
 */
class MQServer
{

    /**
     * exchange 加上厅主 id
     * @param  [type] $exchange [description]
     * @return string [type]           [description]
     */
    protected static function getExchange($exchange): string
    {
        global $app;
        $tid = $app->getContainer()->get('settings')['app']['tid'];
        return $exchange . '_' . $tid;
    }

    /**
     * 消费者
     * @param $exchange
     * @param $queue
     * @param $callback
     * @param array $rabbitmqConf [description]
     * @return void [type]               [description]
     * @throws ErrorException
     */
    public static function startServer($exchange, $queue, $callback, array $rabbitmqConf = [])
    {
        global $app;
        $rabbitmqConf = !empty($rabbitmqConf) ? $rabbitmqConf : $app->getContainer()->get('settings')['rabbitmq'];
        $exchange = self::getExchange($exchange);
        if (isset($rabbitmqConf['ssl'])) {
            $connection = new AMQPSSLConnection(
                $rabbitmqConf['host'],
                $rabbitmqConf['port'],
                $rabbitmqConf['user'],
                $rabbitmqConf['password'],
                $rabbitmqConf['vhost'],
                $rabbitmqConf['ssl'],
                $rabbitmqConf['options']
            );
        } else {
            $connection = new AMQPStreamConnection(
                $rabbitmqConf['host'],
                $rabbitmqConf['port'],
                $rabbitmqConf['user'],
                $rabbitmqConf['password'],
                $rabbitmqConf['vhost'],
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['insist']) ? $rabbitmqConf['options']['insist'] : false,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['login_method']) ? $rabbitmqConf['options']['login_method'] : 'AMQPLAIN',
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['login_response']) ? $rabbitmqConf['options']['login_response'] : null,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['connection_timeout']) ? $rabbitmqConf['options']['locale'] : 'en_US',
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['connection_timeout']) ? $rabbitmqConf['options']['connection_timeout'] : 3,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['read_write_timeout']) ? $rabbitmqConf['options']['read_write_timeout'] : 120,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['context']) ? $rabbitmqConf['options']['context'] : null,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['keepalive']) ? $rabbitmqConf['options']['keepalive'] : false,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['heartbeat']) ? $rabbitmqConf['options']['heartbeat'] : 60,
            );
        }
        $channel = $connection->channel();
        $channel->exchange_declare($exchange, 'fanout', false, true, false);
        $channel->queue_declare($queue, false, false, true, false);
        $channel->queue_bind($queue, $exchange);

        //设置每次只能处理一条消息
        $channel->basic_qos(0, 1, false);
        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    /**
     * 发送广播
     * @param $exchange
     * @param $msg
     * @param array $rabbitmqConf [description]
     * @return void [type]               [description]
     * @throws \Exception
     */
    public static function send($exchange, $msg, array $rabbitmqConf = [])
    {
        global $app;
        $msg = is_array($msg) ? json_encode($msg, JSON_UNESCAPED_UNICODE) : $msg;

        $exchange = self::getExchange($exchange);
        $rabbitmqConf = !empty($rabbitmqConf) ? $rabbitmqConf : $app->getContainer()->get('settings')['rabbitmq'];
        if (isset($rabbitmqConf['ssl'])) {
            $connection = new AMQPSSLConnection(
                $rabbitmqConf['host'],
                $rabbitmqConf['port'],
                $rabbitmqConf['user'],
                $rabbitmqConf['password'],
                $rabbitmqConf['vhost'],
                $rabbitmqConf['ssl'],
                $rabbitmqConf['options']
            );
        } else {
            $connection = new AMQPStreamConnection(
                $rabbitmqConf['host'],
                $rabbitmqConf['port'],
                $rabbitmqConf['user'],
                $rabbitmqConf['password'],
                $rabbitmqConf['vhost'],
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['insist']) ? $rabbitmqConf['options']['insist'] : false,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['login_method']) ? $rabbitmqConf['options']['login_method'] : 'AMQPLAIN',
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['login_response']) ? $rabbitmqConf['options']['login_response'] : null,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['connection_timeout']) ? $rabbitmqConf['options']['locale'] : 'en_US',
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['connection_timeout']) ? $rabbitmqConf['options']['connection_timeout'] : 3,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['read_write_timeout']) ? $rabbitmqConf['options']['read_write_timeout'] : 120,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['context']) ? $rabbitmqConf['options']['context'] : null,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['keepalive']) ? $rabbitmqConf['options']['keepalive'] : false,
                isset($rabbitmqConf['options']) && isset($rabbitmqConf['options']['heartbeat']) ? $rabbitmqConf['options']['heartbeat'] : 60,
            );
        }
        $channel = $connection->channel();
        $msg = new AMQPMessage($msg);
        $channel->exchange_declare($exchange, 'fanout', false, true, false);
        $channel->basic_publish($msg, $exchange);
        $channel->close();
        $connection->close();
    }

}