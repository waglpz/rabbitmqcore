<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exchange\AMQPExchangeType;

trait ExchangeDeclaration
{
    private AMQPChannel $channel;
    private string $exchangeName;
    /** @var ?array<mixed> */
    private ?array $queues;

    /** @param ?array<mixed> $queues */
    public function __construct(
        AMQPChannel $channel,
        string $exchangeName,
        ?array $queues = null,
        string $exchangeTyp = AMQPExchangeType::DIRECT
    ) {
        $this->channel      = $channel;
        $this->exchangeName = $exchangeName;
        $this->queues       = $queues;

        $persistent = true;
        $this->channel->exchange_declare($exchangeName, $exchangeTyp, false, $persistent, false);
        if ($queues === null) {
            return;
        }

        foreach ($queues as $queue) {
            $this->channel->queue_declare($queue['name'], false, $persistent, false, false);

            if (isset($queue['binding_keys'])) {
                foreach ($queue['binding_keys'] as $bindingKey) {
                    $this->channel->queue_bind($queue['name'], $exchangeName, $bindingKey);
                }
            } else {
                $this->channel->queue_bind($queue['name'], $exchangeName);
            }
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $connection = $this->channel->getConnection();
        /** @phpstan-ignore-next-line */
        if ($connection === null) {
            return;
        }

        $connection->close();
    }
}
