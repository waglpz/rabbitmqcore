<?php

declare(strict_types=1);

return [
    'connection'=> include 'connection.php',
    'exchangeDeclarations'=> [
        'exsampleExchangeName' =>[
            'queues'=>[
                'exampleQueueName1' => [], // Parametererweiterung in Zukunft wenn
                'exampleQueueName2' => [],
            ],
        ],
    ],
];
