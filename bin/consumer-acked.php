<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use WAG\RabbitMq\ChannelBuilder;
use WAG\RabbitMq\Consumer;

$config = include __DIR__ . '/../config/main.php';

[
    'hostname' => $hostname,
    'port'     => $port,
    'username' => $username,
    'password' => $password,
    'vhost'    => $vhost,
] = $config['connection'];

$callbackAck = static function (AMQPMessage $message): void {
    echo 'Message received', \PHP_EOL;
    // Do ???
    echo 'Message proceed', \PHP_EOL;
    $message->get('channel')->basic_ack($message->get('delivery_tag'));
};

$channel = (new ChannelBuilder($hostname, $port, $username, $password, $vhost))->channel();

$consumerCfg0  = $config['example_0'];
$consumerAcked = new Consumer(
    $channel,
    $consumerCfg0['exchange']['name'],
    $consumerCfg0['queues'],
    $consumerCfg0['exchange']['type'],
);
$consumerAcked->setCallback($callbackAck);
$consumerAcked->consume();

register_shutdown_function(
    static function () use ($channel): void {
        $channel->close();
        $connection = $channel->getConnection();
        //@phpstan-ignore-next-line
        if ($connection === null) {
            return;
        }

        $connection->close();
    },
);
