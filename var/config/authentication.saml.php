<?php
// Authentication strategy specs

return [
    // Identity field name for user binding; optional, default as `identity`
    'identity_field' => 'identity',

    // Source ID for simplesamlphp
    'source_id'      => 'default-sp',

    // Storage
    'storage'        => [
        'class'   => 'Pi\Authentication\Storage\Session',
        'options' => [
            'namespace' => 'PI_AUTH',
            'member'    => 'member',
        ],
    ],
];
