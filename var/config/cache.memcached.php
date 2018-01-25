<?php
// Cache configuration for Memcached

$config = [
    // Storage adapter
    'adapter' => [
        'name'    => 'memcached',
        // Options, see Zend\Cache\Storage\Adapter\MemcachedOptions
        'options' => [
            'servers' => [
                ['127.0.0.1', 11211],
            ],
        ],
    ],
    // Plugin list
    'plugins' => [
        'exception_handler' => ['throw_exceptions' => false],
    ],
];

return $config;
