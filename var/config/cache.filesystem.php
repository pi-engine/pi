<?php
// Cache configuration for Filesystem

$config = array(
    // Storage adapter
    'adapter'   => array(
        'name'  => 'filesystem',
        // Options, see Zend\Cache\Storage\Adapter\FilesystemOptions
        'options'    => array(
            'cache_dir'         => Pi::path('cache'),
            'dir_level'         => 1,
            'dir_permission'    => 0700,
            'file_permission'   => 0600,
        ),
    ),
    // Plugin list
    'plugins'   => array(
        'exception_handler' => array('throw_exceptions' => false),
    ),
);

return $config;
