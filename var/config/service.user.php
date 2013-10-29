<?php
// User service configuration

return array(
    // Deployment mode: single, provider, consumer, default as single
    'mode'      => 'consumer',

    // User data access adapter
    'adapter'   => 'Pi\User\Adapter\Local',
    'adapter'   => 'Pi\User\Adapter\System',

    // For persistent user data
    'persist'   => array(
        // Expiration
        'ttl'   => 3600,
        // Fields
        'field' => array(
            'id',
            'identity',
            'name',
            'email',
            'avatar',
            'role'
        )
    ),

    // Followings are optional
    'options'   => array(
        // Authentication config
        'authentication'    => 'service.authentication.php',
    ),
);
