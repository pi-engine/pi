<?php
// HTTP client configuration for remote content manipulation

$config = array(
    // Set up default client identifier
    'adapter'   => 'curl',

    // Cache options
    'cache'   => array(
        // Cache storage
        //'storage'   => 'apc',
        // Cache default expiration
        'ttl'       => 3600,
    ),

    // cURL specific configs
    'curl'      => array(
        'timeout'   => 1,
        'httpauth'  => '',
        'username'  => '',
        'password'  => '',
    ),

    // socket specific configs
    'socket'    => array(
    ),
);

return $config;
