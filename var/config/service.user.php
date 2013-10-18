<?php
// User service configuration

return array(
    'adapter'   => 'Pi\User\Adapter\Local',
    'adapter'   => 'Pi\User\Adapter\System',

    'persist'   => array(
        'name',
        'email',
        'avatar',
        'role'
    ),

    // Followings are optional
    'options'   => array(
        'authentication'    => 'service.authentication.php',
    ),
);
