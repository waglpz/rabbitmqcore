<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

final class ConsumerAcked
{
    use ExchangeDeclaration;

    private ?\Closure $callback;

    public function setCallback(\Closure $callback) : void
    {
        $this->callback = $callback;
    }

    public function setPrefetchMessages(int $count) : void
    {
        $this->channel->basic_qos(
            0,
            $count,
            false
        );
    }

    public function consume() : void
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

    public function fetchMessage(string $queueName) : void
    {
        if (! isset($this->callback)) {
            throw new \InvalidArgumentException('Callback not defined.');
        }

        $message = $this->channel->basic_get($queueName);
        if ($message === null) {
            return;
        }

        ($this->callback)($message);
        $this->channel->basic_ack($message->get('delivery_tag'));
    }
}
