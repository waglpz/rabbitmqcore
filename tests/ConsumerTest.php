<?php

declare(strict_types=1);

namespace WAG\RabbitMq\Tests;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use WAG\RabbitMq\Consumer;

class ConsumerTest extends TestCase
{
    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function consumeThrowsAnInvalidArgumentExceptionWhenCallbackNotDefined(): void
    {
        $queues   = [['name' => 'test_queue']];
        $channel  = $this->createMock(AMQPChannel::class);
        $consumer = new Consumer($channel, 'exchange_test', $queues);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Callback not defined.');
        $consumer->consume();
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function consumeThrowsAnInvalidArgumentExceptionWhenQueueNotDefined(): void
    {
        $channel  = $this->createMock(AMQPChannel::class);
        $consumer = new Consumer($channel, 'exchange_test');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Queues are not yet defined?');
        $consumer->consume();
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function fetchThrowsAnInvalidArgumentExceptionWhenCallbackNotDefined(): void
    {
        $channel  = $this->createMock(AMQPChannel::class);
        $consumer = new Consumer($channel, 'exchange_test');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Callback not defined.');
        $consumer->fetchMessage('test_queue');
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function fetchMessageReturnsNull(): void
    {
        $callback = static function (): void {
            self::fail('ERROR: callback should not to be executed.');
        };
        $channel  = $this->createMock(AMQPChannel::class);
        $channel->expects(self::once())
                ->method('basic_get')
                ->with('test_queue')
                ->willReturn(null);
        $consumer = new Consumer($channel, 'exchange_test');
        $consumer->setCallback($callback);
        $message = $consumer->fetchMessage('test_queue');
        self::assertNull($message);
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function fetchMessageAndCallbackReturnsNull(): void
    {
        $callback = static fn (AMQPMessage $message) => null;
        $channel  = $this->createMock(AMQPChannel::class);
        $channel->expects(self::once())
                ->method('basic_get')
                ->with('test_queue')
                ->willReturn(null);
        $consumer = new Consumer($channel, 'exchange_test');
        $consumer->setCallback($callback);
        $message = $consumer->fetchMessage('test_queue');
        self::assertNull($message);
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function fetchMessageAndCallbackReturnsAMessage(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $message = $this->createMock(AMQPMessage::class);
        $message->expects(self::exactly(2))->method('get')
                ->withConsecutive(['channel'], ['delivery_tag'])
                ->willReturnOnConsecutiveCalls($channel, 'TAG');

        $channel->expects(self::once())->method('basic_ack')
                ->willReturn('TAG');
        $channel->expects(self::once())
                ->method('basic_get')
                ->with('test_queue')
                ->willReturn($message);
        $consumer = new Consumer($channel, 'exchange_test');
        $callback = static fn (AMQPMessage $message): AMQPMessage => $message;
        $consumer->setCallback($callback);
        $factMessage = $consumer->fetchMessage('test_queue');
        self::assertSame($factMessage, $message);
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function fetchMessageDoNoAck(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $message = $this->createMock(AMQPMessage::class);
        $message->expects(self::never())->method('get');
        $channel->expects(self::never())->method('basic_ack');
        $channel->expects(self::once())
                ->method('basic_get')
                ->with('test_queue')
                ->willReturn($message);
        $consumer = new Consumer($channel, 'exchange_test');
        $callback = static fn (AMQPMessage $message): AMQPMessage => $message;
        $consumer->setCallback($callback);
        $factMessage = $consumer->fetchMessage('test_queue', false);
        self::assertSame($factMessage, $message);
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function fetchThrowsLogicExceptionExceptionWhenCallbackReturnsNotSameMessage(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $message = $this->createMock(AMQPMessage::class);
        $channel->expects(self::once())
                ->method('basic_get')
                ->with('test_queue')
                ->willReturn($message);
        $consumer = new Consumer($channel, 'exchange_test');
        $callback = static fn (AMQPMessage $message): AMQPMessage => clone $message;
        $consumer->setCallback($callback);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Callback does not returns same message instance or null as expected.');
        $consumer->fetchMessage('test_queue', false);
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function consumeMessages(): void
    {
        $queues   = [
            [
                'name'         => 'test_queue',
                'binding_keys' => [
                    'v1',
                    'v2',
                ],
            ],
            [
                'name'         => 'test_queue_2',
                'binding_keys' => [
                    'v1',
                    'v2',
                ],
            ],
        ];
        $callback = static fn () => null;

        $channel = $this->createMock(AMQPChannel::class);
        $channel->expects(self::once())->method('basic_ack')->with('sss');
        $channel->expects(self::once())
                ->method('basic_qos')
                ->with(0, 10, false);
        $channel->expects(self::exactly(2))->method('is_consuming')
                ->willReturnOnConsecutiveCalls(true, false);
        $channel->expects(self::once())->method('wait');
        $channel->expects(self::exactly(2))
                ->method('basic_consume')
                ->withConsecutive(
                    [
                        'test_queue',
                        'test_queueConsumer',
                        false,
                        false,
                        false,
                        false,
                        $callback,
                    ],
                    [
                        'test_queue_2',
                        'test_queue_2Consumer',
                        false,
                        false,
                        false,
                        false,
                        $callback,
                    ]
                );
        $message = $this->createMock(AMQPMessage::class);
        $message->expects(self::exactly(2))->method('get')
                ->withConsecutive(['channel'], ['delivery_tag'])
                ->willReturnOnConsecutiveCalls($channel, 'sss');

        $consumer = new Consumer($channel, 'exchange_test', $queues);
        $consumer->setCallback($callback);
        $consumer->setPrefetchMessages(10);
        $consumer->acknowledgesMessage($message);
        $consumer->consume();
    }

    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function channelAndConnectionAreClosedOnExit(): void
    {
        $connection = $this->createMock(AbstractConnection::class);
        $connection->expects(self::once())->method('close');
        $channel = $this->createMock(AMQPChannel::class);
        $channel->expects(self::once())->method('close');
        $channel->expects(self::once())->method('getConnection')->willReturn($connection);
        (new Consumer($channel, 'test_exchange'));
    }
}
