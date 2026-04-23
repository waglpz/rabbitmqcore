<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

trait MessageFetcher
{
    /** @var array<string, mixed> */
    private array $callback;
    /** @var array<string, Consumer> */
    private array $consumer;
    /** @var array<mixed> */
    private static array $configMqConnection;
    /** @var array<mixed> */
    private static array $configQueues;

    public function consumer(string $id): Consumer
    {
        if (isset($this->consumer[$id])) {
            return $this->consumer[$id];
        }

        if (! isset($this->callback[$id])) {
            throw new \Error('Callback is not yet defined, must be defined.');
        }

        $channelBuilder = new ChannelBuilder(
            self::$configMqConnection['hostname'],
            self::$configMqConnection['port'],
            self::$configMqConnection['username'],
            self::$configMqConnection['password'],
            self::$configMqConnection['vhost'],
        );

        if (! isset(self::$configQueues[$id])) {
            throw new \Error('Unbenannte Queue Definition für Id: ' . $id . '.');
        }

        $channel             = $channelBuilder->channel();
        $this->consumer[$id] = new Consumer(
            $channel,
            self::$configQueues[$id]['exchange']['name'],
            self::$configQueues[$id]['queues'],
        );

        $this->consumer[$id]->setCallback($this->callback[$id]);

        return $this->consumer[$id];
    }

    public function onFetchCallback(mixed $callback, string $id): void
    {
        $this->callback[$id] = $callback;
    }

    /**
     * @param mixed[] $configMqConnection
     * @param mixed[] $configQueues
     */
    public function setMQConfig(array $configMqConnection, array $configQueues): void
    {
        self::$configMqConnection = $configMqConnection;
        self::$configQueues       = $configQueues;
    }
}
