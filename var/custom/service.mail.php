<?php
// Mail service configuration

$config = array(
    // Set up default mail transport identifier
    //'transport' => 'sendmail',
    'transport' => 'smtp',

    // SMTP
    'smtp'      => array(
        'name'              => 'smtp',
        'host'              => '***',
        'port'              => 25,
        'connection_class'  => 'login',
        'connection_config' => array(
            'username' => '***',
            'password' => '***',
        ),
    )
);

return $config;
