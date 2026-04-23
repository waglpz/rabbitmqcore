<?php

declare(strict_types=1);

namespace WAG\RabbitMq\Tests;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use WAG\RabbitMq\ProducerConfirmed;

final class ProducerConfirmedTest extends TestCase
{
    /**
     * @test
     * @covers \WAG\RabbitMq\ProducerConfirmed
     * @throws Exception
     */
    public function publishMessage(): void
    {
        $exchangeName = 'test_exchange';
        $queues       = [
            [
                'name'        => 'test_queue',
                'binding_key' => 'v1',
            ],
        ];
        $message      = $this->createMock(AMQPMessage::class);
        $message->expects(self::once())->method('set')->with('delivery_mode', AMQPMessage::DELIVERY_MODE_PERSISTENT);
        $channel = $this->createMock(AMQPChannel::class);
        $channel->expects(self::once())->method('basic_publish')->with($message, $exchangeName, 'v1');
        $channel->expects(self::once())->method('wait_for_pending_acks');
        $publisher = new ProducerConfirmed($channel, $exchangeName, $queues);
        $publisher->publish($message, 'v1');
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\ProducerConfirmed
     * @throws Exception
     */
    public function channelAndConnectionAreClosedOnExit(): void
    {
        $connection = $this->createMock(AbstractConnection::class);
        $connection->expects(self::once())->method('close');
        $channel = $this->createMock(AMQPChannel::class);
        $channel->expects(self::once())->method('close');
        $channel->expects(self::once())->method('getConnection')->willReturn($connection);
        (new ProducerConfirmed($channel, 'test_exchange'));
    }
}
