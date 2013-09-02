<?php
// User service configuration

return array(
    'adapter'   => 'Pi\User\Adapter\Local',
    'adapter'   => 'Pi\User\Adapter\System',

    // Followings are optional
    'options'   => array(
        'authentication'    => 'service.authentication.php',
    ),
);
