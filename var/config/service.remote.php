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

    //'cache'   => false,

    // cURL specific configs
    'curl'      => array(
        'timeout'   => 1,
        'maxredirects'  => 10,
        'curloptions'   => array(
            CURLOPT_FOLLOWLOCATION  => true,

        ),
    ),

    // socket specific configs
    'socket'    => array(
    ),
);

return $config;
