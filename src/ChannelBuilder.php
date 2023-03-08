<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Webmozart\Assert\Assert;

final class ChannelBuilder
{
    private AMQPStreamConnection $connection;

    public function __construct(
        private readonly string $hostname,
        private readonly string $port,
        private readonly string $username,
        private readonly string $password,
        private readonly string $vhost,
    ) {
    }

    /** @throws \Exception */
    public function channelPublishConfirmed(callable $ack, callable $nack): AMQPChannel
    {
        $channel = $this->getConnection()->channel();
        $channel->set_ack_handler($ack);
        $channel->set_nack_handler($nack);
        $channel->confirm_select();

        return $channel;
    }

    /** @throws \Exception */
    public function getConnection(): AMQPStreamConnection
    {
        Assert::integerish($this->port);
        $port = (int) $this->port;

        return $this->connection ??
            $this->connection = new AMQPStreamConnection(
                $this->hostname,
                $port,
                $this->username,
                $this->password,
                $this->vhost,
            );
    }

    public function setConnection(AMQPStreamConnection $connection): void
    {
        $this->connection = $connection;
    }

    /** @throws \Exception */
    public function channel(): AMQPChannel
    {
        return $this->getConnection()->channel();
    }
}
