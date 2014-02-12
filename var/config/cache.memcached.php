<?php
// Cache configuration for Memcached

$config = array(
    // Storage adapter
    'adapter'   => array(
        'name'  => 'memcached',
        // Options, see Zend\Cache\Storage\Adapter\MemcachedOptions
        'options'    => array(
            'servers'   => array(
                array('127.0.0.1', 11211),
            ),
        ),
    ),
    // Plugin list
    'plugins'   => array(
        'exception_handler' => array('throw_exceptions' => false),
    ),
);

return $config;
