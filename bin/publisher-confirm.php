<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use WAG\RabbitMq\ChannelBuilder;
use WAG\RabbitMq\ProducerConfirmed;

$config = include __DIR__ . '/../config/main.php';

[
    'hostname' => $hostname,
    'port' => $port,
    'username' => $username,
    'password' => $password,
] = $config['connection'];

$channelBuilder = new ChannelBuilder(
    $hostname,
    $port,
    $username,
    $password
);

$ackFunction = static function (): void {
    echo 'Message done',\PHP_EOL;
};

$nackFunction = static function (): void {
    echo 'Message not done ! Error !',\PHP_EOL;
};


$channel = $channelBuilder->channelPublishConfirmed($ackFunction, $nackFunction);

$example0 = $config['example_0'];
$producer = new ProducerConfirmed($channel, $example0['exchange']['name'], $example0['queues']);
$message  = new AMQPMessage('{"name":"krueger","vorname":"Lutz","properties":{"alter":"58"}');

$producer->publish($message, 'v1');
$producer->publish($message, 'v2');

$channel  = $channelBuilder->channelPublishConfirmed($ackFunction, $nackFunction);
$example1 = $config['example_1'];
$producer = new ProducerConfirmed($channel, $example1['exchange']['name'], $example1['queues']);
$message  = new AMQPMessage('{"name":"krueger","vorname":"Lutz","properties":{"alter":"58"}');

$producer->publish($message, 'v3');
