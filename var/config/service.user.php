<?php
// User service configuration

return array(
    // User data access adapter
    'adapter'   => 'system',

    'system'    => array(
        'class' => 'Pi\User\Adapter\System',
        'options'   => array(
            // Authentication config
            'authentication'    => 'service.authentication.php',
        ),
    ),

    'options'   => array(
        // For persistent user data
        'persist'   => array(
            // Expiration, in seconds
            'ttl'   => 300,
            // Fields
            'field' => array(
                'id',
                'identity',
                'name',
                'email',
                'avatar',
                'role'
            ),
        ),
    ),

    // Following are adapter specs

    // Local user
    'local'    => array(
        'class' => 'Pi\User\Adapter\Local',
        'options'   => array(
            // Authentication config
            'authentication'    => 'service.authentication.php',
        ),
    ),

    // Client user
    'client'    => array(
        'class' => 'Pi\User\Adapter\Client',
        'options'   => array(
            // Authentication config
            'authentication'    => 'service.authentication.php',
            /*
            // Resources
            'resource'  => array(
                'timeline'  => 'Module\Uclient\Api\Resource\Timeline',
                'message'   => 'Module\Uclient\Api\Resource\Message',
                'relation'  => 'Module\Uclient\Api\Resource\Relation',
            ),
            */
        ),
    ),

);
