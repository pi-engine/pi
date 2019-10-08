<?php
// User service configuration

return [
    // User data access adapter
    'adapter' => 'system',

    'system' => [
        'class'   => 'Pi\User\Adapter\System',
        'options' => [],
    ],

    'options' => [
        // For persistent user data
        'persist' => [
            // Expiration, in seconds
            'ttl'   => 300,
            // Fields
            'field' => [
                'id',
                'identity',
                'name',
                'email',
                'avatar',
                'role',
            ],
        ],
    ],

    // Following are adapter specs

    // Local user
    'local'   => [
        'class'   => 'Pi\User\Adapter\Local',
        'options' => [],
    ],

    // Client user
    'client'  => [
        'class'   => 'Pi\User\Adapter\Client',
        'options' => [],
    ],

];
