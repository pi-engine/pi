<?php
// Cache configuration for redis

$config = [
    // Storage adapter
    'adapter' => [
        'name'    => 'redis',
        'options' => [
            'server' => ['127.0.0.1', 6379],
        ],
    ],
    // Plugin list
    'plugins' => [
        'exception_handler' => ['throw_exceptions' => false],
    ],
];

return $config;
