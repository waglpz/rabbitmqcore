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

$exchangeName         = 'exsampleExchangeName';
$exchangeDeclarations = $config['exchangeDeclarations'][$exchangeName];

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

$producer = new ProducerConfirmed(
    $channel,
    $exchangeName,
    \array_keys($exchangeDeclarations['queues'])
);

$message = new AMQPMessage('{"name":"krueger","vorname":"Lutz","properties":{"alter":"58"}');
$producer->publish($message);
