<?php

declare(strict_types=1);

$default = [
    'hostname' => '10.120.5.2',
    'port' => '5672',
    'username' => 'guest',
    'password' => 'guest',
];

$envSpecificConfig = __DIR__ . 'connection.' . getenv('APP_ENV') . '.php';

return is_file($envSpecificConfig) ? array_replace_recursive($default, include $envSpecificConfig) : $default;
