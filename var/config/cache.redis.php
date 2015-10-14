<?php
// Cache configuration for redis

$config = array(
    // Storage adapter
    'adapter'   => array(
        'name'  => 'redis',
        'options'    => array(
            'server'   => array('127.0.0.1', 6379),
        ),
    ),
    // Plugin list
    'plugins'   => array(
        'exception_handler' => array('throw_exceptions' => false),
    ),
);

return $config;
