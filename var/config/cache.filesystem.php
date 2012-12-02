<?php
// Cache configuration for Filesystem

$config = array(
    // Storage adapter
    'adapter'   => array(
        'name'  => 'filesystem',
        // Options
        'options'    => array(
            //'namespace' => Pi::config('identifier'),
            'cache_dir' => Pi::path('cache'),
            'dir_level' => 1,
        ),
    ),
    // Plugin list
    'plugins'   => array(
        'exception_handler' => array('throw_exceptions' => false),
    ),
);

return $config;
