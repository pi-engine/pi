<?php
// HTTP client configuration for remote content manipulation

$config = [
    // Set up default client identifier
    'adapter'       => 'curl',

    // For http authorization
    'authorization' => [
        'httpauth' => '',
        'username' => '',
        'password' => '',
    ],

    // Cache options
    'cache'         => [
        // Cache storage
        //'storage'   => 'apc',
        // Cache default expiration
        'ttl' => 3600,
    ],
    // Disable cache, comment out this in production
    //'cache'   => false,

    // cURL specific configs
    'curl'          => [
        // Timeout for connection, in seconds
        'timeout'      => 60,
        'maxredirects' => 10,
        'curloptions'  => [
            CURLOPT_FOLLOWLOCATION => true,

            // Skip cert verification, set to TRUE in production
            CURLOPT_SSL_VERIFYPEER => false,
        ],
    ],

    // socket specific configs
    'socket'        => [
    ],
];

return $config;
