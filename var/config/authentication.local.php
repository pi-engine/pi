<?php
// Authentication strategy specs

return [
    // Identity field name for user binding; optional, default as `id`
    'identity_field' => 'id',

    // Storage
    'storage'        => [
        'class'   => 'Pi\Authentication\Storage\Session',
        'options' => [
            'namespace' => 'PI_AUTH',
            'member'    => 'member',
        ],
    ],

    // Adapter
    'adapter'        => [
        // Adapter class
        'class'   => 'Pi\Authentication\Adapter\DbTable',
        'options' => [
            // Database table for user account
            'table_name'        => 'user_account',
            // Identity column for authentication
            'identity_column'   => 'identity',
            // Credential column for authentication
            'credential_column' => 'credential',
            // Callback for authentication query check
            'callback'          => function ($a, $b, $identity) {
                return $identity['active']
                    && $a === $identity->transformCredential($b);
            },

            // Columns to return from valid authentication result
            'return_columns'    => ['id', 'identity'],
            // Columns to omit, applicable only if `return_columns` not specified
            //'omit_columns'      => array('credential', 'salt'),
        ],
    ],
];
