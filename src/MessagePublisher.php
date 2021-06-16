<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

trait MessagePublisher
{
    private \Closure $ackFunction;
    private \Closure $nackFunction;
    /** @var array<ProducerConfirmed> */
    private array $publisher;
    /** @var array<mixed> */
    private static array $configMqConnection;
    /** @var array<mixed> */
    private static array $configQueues;

    public function onPublishAckCallback(\Closure $function): void
    {
        $this->ackFunction = $function;
    }

    public function onPublishNackCallback(\Closure $function): void
    {
        $this->nackFunction = $function;
    }

    public function publisher(string $id): ProducerConfirmed
    {
        if (! isset($this->publisher[$id])) {
            if (! isset(self::$configQueues[$id])) {
                throw new \Error('Unbenannte Queue Definition für Id: ' . $id . '.');
            }

            $channelBuilder = new ChannelBuilder(
                self::$configMqConnection['hostname'],
                self::$configMqConnection['port'],
                self::$configMqConnection['username'],
                self::$configMqConnection['password'],
                self::$configMqConnection['vhost']
            );

            $this->ackFunction ?? $this->ackFunction = static function (): void {
                echo 'Message done !';
            };

            $this->nackFunction ?? $this->nackFunction = static function (): void {
                throw new \Error('Message not published - unknown Error.');
            };

            $channel = $channelBuilder->channelPublishConfirmed($this->ackFunction, $this->nackFunction);

            $this->publisher[$id] = new ProducerConfirmed(
                $channel,
                self::$configQueues[$id]['exchange']['name'],
                self::$configQueues[$id]['queues'],
            );
        }

        return $this->publisher[$id];
    }

    /**
     * @param mixed[] $configMqConnection
     * @param mixed[] $configQueues
     */
    public function setConfig(array $configMqConnection, array $configQueues): void
    {
        self::$configMqConnection = $configMqConnection;
        self::$configQueues       = $configQueues;
    }
}
