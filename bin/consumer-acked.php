<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use WAG\RabbitMq\ChannelBuilder;
use WAG\RabbitMq\ConsumerAcked;

$config = include __DIR__ . '/../config/main.php';

[
    'hostname' => $hostname,
    'port' => $port,
    'username' => $username,
    'password' => $password,
] = $config['connection'];

$callbackAck = static function (AMQPMessage $message): void {
    echo 'Message received', \PHP_EOL;
    // Do ???
    echo 'Message proceed', \PHP_EOL;
    $message->get('channel')->basic_ack($message->get('delivery_tag'));
};

$channel = (new ChannelBuilder($hostname, $port, $username, $password))->channelConsumerAcked();
// todo: prefetch mode in Channel setzen !

$exchangeName         = 'exsampleExchangeName';
$exchangeDeclarations = $config['exchangeDeclarations'][$exchangeName];

$consumerAcked = new ConsumerAcked($channel, $exchangeName, \array_keys($exchangeDeclarations['queues']));
$consumerAcked->setCallback($callbackAck);
$consumerAcked->consume();

register_shutdown_function(static function () use ($channel): void {
    $channel->close();
    $connection = $channel->getConnection();
    //@phpstan-ignore-next-line
    if ($connection === null) {
        return;
    }

    $connection->close();
});
