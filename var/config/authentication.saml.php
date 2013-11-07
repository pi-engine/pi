<?php
// Authentication strategy configuration

return array(
    // Source ID for simplesamlphp
    'source_id' => md5(uniqid()),

    // Storage
    'storage'   => array(
        'class' => 'Pi\Authentication\Storage\Session',
        'options' => array(
            'namespace' => 'PI_AUTH',
            'member'    => 'member',
        ),
    ),
);
