<?php
// Authentication service configuration

return array(
    /*
    'strategy'   => 'Saml',
    */

    'strategy'   => array(
        'class'     => 'Local',
        'options'   => array(
            // Storage
            'storage'   => array(
                'class' => 'Pi\Authentication\Storage\Session',
                'options' => array(
                    'namespace' => 'PI_AUTH',
                    'member'    => 'member',
                ),
            ),
            // Adapter
            'adapter'  => array(
                'class'     => 'Pi\Authentication\Adapter\DbTable',
                'options'   => array(
                    'table_name'        => 'user_account',
                    'identity_column'   => 'identity',
                    'credential_column' => 'credential',
                    // Callback for authentication query check
                    'callback'          => function ($a, $b, $identity) {
                            return $identity['active']
                            && $a === $identity->transformCredential($b);
                        },

                    'return_columns'    => array('id', 'identity'),
                    //'omit_columns'      => array('credential', 'salt'),
                ),
            ),
        ),
    ),
);
