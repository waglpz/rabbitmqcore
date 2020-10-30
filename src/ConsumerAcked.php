<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;

final class ConsumerAcked
{
    use ExchangeDeclaration;

    private ?\Closure $callback;

    public function setCallback(\Closure $callback): void
    {
        $this->callback = $callback;
    }

    public function setPrefetchMessages(int $count): void
    {
        $this->channel->basic_qos(
            0,
            $count,
            false
        );
    }

    public function consume(): void
    {
        if ($this->queues === null || \count($this->queues) < 1) {
            throw new \InvalidArgumentException('Queues are not yet defined?');
        }

        if (! isset($this->callback)) {
            throw new \InvalidArgumentException('Callback not defined.');
        }

        foreach ($this->queues as $queue) {
            $this->channel->basic_consume($queue, $queue . 'Consumer', false, false, false, false, $this->callback);
        }

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function acknowledgesMessage(AMQPMessage $message): void
    {
        $message->get('channel')->basic_ack($message->get('delivery_tag'));
    }

    public function fetchMessage(string $queueName, bool $doAck = true): ?AMQPMessage
    {
        if (! isset($this->callback)) {
            throw new \InvalidArgumentException('Callback not defined.');
        }

        $message = $this->channel->basic_get($queueName);
        if ($message === null) {
            return null;
        }

        $eventualMessage = ($this->callback)($message);

        if ($eventualMessage !== null) {
            if ($eventualMessage !== $message) {
                throw new \LogicException('Callback does not returns same message instance or null as expected.');
            }

            if ($doAck) {
                $this->acknowledgesMessage($message);
            }
        }

        return $eventualMessage;
    }
}
