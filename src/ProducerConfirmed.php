<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;

final class ProducerConfirmed
{
    use ExchangeDeclaration;

    public function publish(AMQPMessage $message) : void
    {
        $message->set('delivery_mode', AMQPMessage::DELIVERY_MODE_PERSISTENT);
        $this->channel->basic_publish($message, $this->exchangeName);
        $this->channel->wait_for_pending_acks();
    }

    public function __destroy() : void
    {
        $this->channel->close();
        $this->channel->getConnection()->close();
    }
}
