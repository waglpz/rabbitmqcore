<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;

final class ProducerConfirmed
{
    use ExchangeDeclaration;

    public function publish(AMQPMessage $message, string $routingKey = ''): void
    {
        $message->set('delivery_mode', AMQPMessage::DELIVERY_MODE_PERSISTENT);
        $this->channel->basic_publish($message, $this->exchangeName, $routingKey);
        $this->channel->wait_for_pending_acks();
    }

    public function __destruct()
    {
        $this->channel->close();
        $connection = $this->channel->getConnection();
        // @phpstan-ignore-next-line
        if ($connection === null) {
            return;
        }

        $connection->close();
    }
}
