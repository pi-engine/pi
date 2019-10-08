<?php
// Mail service configuration

$config = [
    // Set up default mail transport identifier
    'transport' => 'sendmail',

    // SMTP
    'smtp'      => [
        'name'              => 'localhost',
        'host'              => '127.0.0.1',
        'port'              => null,
        'connection_class'  => 'plain',
        'connection_config' => [
            'usename'  => '',
            'password' => '',
            'ssl'      => 'ssl',
        ],
    ],

    // File
    'file'      => [
    ],
];

return $config;
