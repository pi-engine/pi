<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Permission specs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
return [
    // Front section
    'front' => [
        'profile-page' => [
            'title'  => _a('Profile pages'),
            'access' => [
                'member',
            ],
        ],
    ],
    // Admin section
    'admin' => [
        'account' => [
            'title'  => _a('User account'),
            'access' => [
                //'admin',
            ],
        ],
        'user'    => [
            'title'  => _a('User profile'),
            'access' => [
                //'admin',
            ],
        ],
        'role'    => [
            'title'  => _a('Roles'),
            'access' => [
                //'admin',
            ],
        ],
        'profile' => [
            'title'  => _a('Profile fields'),
            'access' => [
                //'admin',
            ],
        ],
        /*
        'import'      => array(
            'title'         => _a('Import'),
            'access'        => array(
                //'admin',
            ),
        ),
        */

        'avatar'      => [
            'title'  => _a('Avatars'),
            'access' => [
                //'admin',
            ],
        ],
        'form'        => [
            'title'  => _a('Forms'),
            'access' => [
                //'admin',
            ],
        ],
        'plugin'      => [
            'title'  => _a('Plugin management'),
            'access' => [
                //'admin',
            ],
        ],
        'maintenance' => [
            'title'  => _a('Maintenance'),
            'access' => [
                //'admin',
            ],
        ],
        'inquiry'     => [
            'title'  => _a('Inquiry'),
            'access' => [
                //'admin',
            ],
        ],
        'condition'   => [
            'title'  => _a('Terms and conditions'),
            'access' => [
                //'admin',
            ],
        ],
    ],
];
