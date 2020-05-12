<?php
// Cache configuration for Filesystem

$config = [
    // Storage adapter
    'adapter' => [
        'name'    => 'filesystem',
        // Options, see Laminas\Cache\Storage\Adapter\FilesystemOptions
        'options' => [
            'cache_dir'       => Pi::path('cache'),
            'dir_level'       => 1,
            'dir_permission'  => 0700,
            'file_permission' => 0600,
        ],
    ],
    // Plugin list
    'plugins' => [
        'exception_handler' => ['throw_exceptions' => false],
    ],
];

return $config;
