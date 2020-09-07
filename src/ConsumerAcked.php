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
}
