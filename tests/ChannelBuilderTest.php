<?php

declare(strict_types=1);

namespace WAG\RabbitMq\Tests;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use WAG\RabbitMq\ChannelBuilder;

class ChannelBuilderTest extends TestCase
{
    /**
     * @test
     * @covers \WAG\RabbitMq\ChannelBuilder
     */
    public function canCreateChannelConsumer(): void
    {
        $host           = 'test.test';
        $port           = '1234';
        $user           = 'test';
        $password       = 'test';
        $vhost          = '/test';
        $channelBuilder = new ChannelBuilder(
            $host,
            $port,
            $user,
            $password,
            $vhost,
        );
        $channel        = $this->createMock(AMQPChannel::class);
        $connection     = $this->createMock(AMQPStreamConnection::class);
        $connection->expects(self::once())->method('channel')->willReturn($channel);
        $channelBuilder->setConnection($connection);
        $channelBuilder->channel();
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\ChannelBuilder
     */
    public function canCreateChannelPublishConfirmed(): void
    {
        $host           = 'test.test';
        $port           = '1234';
        $user           = 'test';
        $password       = 'test';
        $vhost          = '/test';
        $channelBuilder = new ChannelBuilder(
            $host,
            $port,
            $user,
            $password,
            $vhost,
        );
        $ackCallback    = static fn () => null;
        $nackCallback   = static fn () => null;
        $channel        = $this->createMock(AMQPChannel::class);
        $channel->expects(self::once())->method('set_ack_handler')->with($ackCallback);
        $channel->expects(self::once())->method('set_nack_handler')->with($nackCallback);
        $channel->expects(self::once())->method('confirm_select');
        $connection = $this->createMock(AMQPStreamConnection::class);
        $connection->expects(self::once())->method('channel')->willReturn($channel);
        $channelBuilder->setConnection($connection);
        $channelBuilder->channelPublishConfirmed($ackCallback, $nackCallback);
    }
}
