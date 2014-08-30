<?php
// HTTP client configuration for remote content manipulation

$config = array(
    // Set up default client identifier
    'adapter'   => 'curl',

    // For http authorization
    'authorization' => array(
        'httpauth'  => '',
        'username'  => '',
        'password'  => '',
    ),

    // Cache options
    'cache'   => array(
        // Cache storage
        //'storage'   => 'apc',
        // Cache default expiration
        'ttl'       => 3600,
    ),
    // Disable cache, comment out this in production
    //'cache'   => false,

    // cURL specific configs
    'curl'      => array(
        // Timeout for connection, in seconds
        'timeout'   => 1,
        'maxredirects'  => 10,
        'curloptions'   => array(
            CURLOPT_FOLLOWLOCATION  => true,

            // Skip cert verification, set to TRUE in production
            CURLOPT_SSL_VERIFYPEER  => false,
        ),
    ),

    // socket specific configs
    'socket'    => array(
    ),
);

return $config;
