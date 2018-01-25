<?php
// Cache configuration for APC

$config = [
    // Storage adapter
    'adapter' => [
        'name'    => 'apc',
        // Options, see Zend\Cache\Storage\Adapter\ApcOptions
        'options' => [
        ],
    ],
    // Plugin list
    'plugins' => [
        'exception_handler' => ['throw_exceptions' => false],
    ],
];

return $config;
