<?php
// Rendering cache service configuration

return array(
    // Do not specify storage and use storage specified by generic cache service
    'storage'   => '',


    // Use generic filesystem cache service explicitly
    //'storage'   => 'filesystem',


    // Use generic memcached cache service explicitly
    //'storage'   => 'memcached',

    // Use filesystem cache service with custom configs
    /*
    'storage'   => array(
        // Storage adapter
        'adapter'   => array(
            'name'  => 'filesystem',
            // Options, see Zend\Cache\Storage\Adapter\FilesystemOptions
            'options'    => array(
                'cache_dir'         => '/path/to/custom/cache/path',
                'dir_level'         => 1,
                'dir_permission'    => 0700,
                'file_permission'   => 0600,
            ),
        ),
        // Plugin list
        'plugins'   => array(
            'exception_handler' => array('throw_exceptions' => false),
        ),
    ),
    */

    // Use memcached cache service with custom configs
    /*
    'storage'   => array(
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
    ),
    */
);