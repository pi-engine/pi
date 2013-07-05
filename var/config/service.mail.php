<?php
// Mail service configuration

$config = array(
    // Set up default mail transport identifier
    'transport' => 'sendmail',

    // SMTP
    'smtp'      => array(
        'name'      => 'localhost',
        'host'      => '127.0.0.1',
        'port'      => null,
        'connection_class'  => 'plain',
        'connection_config' => array(
            'usename'   => '',
            'password'  => '',
            'ssl'       => 'ssl',
        ),
    ),

    // File
    'file'  => array(
    ),
);

return $config;
