<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;

interface Producer
{
    public function publish(AMQPMessage $message, string $routingKey = ''): void;

    public function __destruct();
}
