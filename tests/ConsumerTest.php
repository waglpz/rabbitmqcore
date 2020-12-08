<?php

declare(strict_types=1);

namespace WAG\RabbitMq\Tests;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use WAG\RabbitMq\Consumer;

class ConsumerTest extends TestCase
{
    /**
     * @test
     * @covers \WAG\RabbitMq\Consumer
     */
    public function throwsAnInvalidArgumentExceptionWhenCallbackNotDefined(): void
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
}
