<?php

declare(strict_types=1);

use PhpAmqpLib\Exchange\AMQPExchangeType;

return [
    'connection' => include 'connection.php',

    'example_0' => [
        'queues' => [
            [
                'name' => 'example-queue',
                'binding_keys' => [
                    'v1',
                    'v2',
                ],
            ],
        ],
        'exchange' => [
            'name' => 'example-exchange',
            'type' => AMQPExchangeType::DIRECT,
        ],
    ],

    'example_1' => [
        'queues' => [
            [
                'name' => 'example-queue-2',
                'binding_keys' => ['v3'],
            ],
        ],
        'exchange' => [
            'name' => 'example-exchange',
            'type' => AMQPExchangeType::DIRECT,
        ],
    ],
];
