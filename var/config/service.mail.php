<?php
// Mail service configuration

$config = [
    // Set mail class
    'class'     => 'laminas-mail',

    // Set up default mail transport identifier
    'transport' => 'sendmail',

    // SMTP
    'smtp'      => [
        'name'              => 'localhost',
        'host'              => '127.0.0.1',
        'port'              => null,
        'connection_class'  => 'plain',
        'connection_config' => [
            'username' => '',
            'password' => '',
            'ssl'      => 'ssl', // ssl, tls or ...
            'port'     => '',
            'option'   => [
                'ssl' => [
                    'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                ],
            ],
        ],
    ],

    // File
    'file'      => [
    ],
];

return $config;