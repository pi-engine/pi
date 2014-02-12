<?php
// Authentication strategy specs

return array(
    // Identity field name for user binding; optional, default as `identity`
    'identity_field'    => 'identity',

    // Source ID for simplesamlphp
    'source_id' => 'default-sp',

    // Storage
    'storage'   => array(
        'class' => 'Pi\Authentication\Storage\Session',
        'options' => array(
            'namespace' => 'PI_AUTH',
            'member'    => 'member',
        ),
    ),
);
