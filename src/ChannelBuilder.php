<?php

declare(strict_types=1);

namespace WAG\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class ChannelBuilder
{
    private string $hostname;
    private string $port;
    private string $username;
    private string $vhost;

    private string $password;
    private AMQPStreamConnection $connection;

    public function __construct(string $hostname, string $port, string $username, string $password, string $vhost)
    {
        $this->hostname = $hostname;
        $this->port     = $port;
        $this->username = $username;
        $this->password = $password;
        $this->vhost    = $vhost;
    }

    public function channelPublishConfirmed(callable $ack, callable $nack): AMQPChannel
    {
        $channel = $this->getConnection()->channel();
        $channel->set_ack_handler($ack);
        $channel->set_nack_handler($nack);
        $channel->confirm_select();

        return $channel;
    }

    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection ??
            $this->connection = new AMQPStreamConnection(
                $this->hostname,
                $this->port,
                $this->username,
                $this->password,
                $this->vhost
            );
    }

    public function setConnection(AMQPStreamConnection $connection): void
    {
        $this->connection = $connection;
    }

    public function channel(): AMQPChannel
    {
        return $this->getConnection()->channel();
    }
}
