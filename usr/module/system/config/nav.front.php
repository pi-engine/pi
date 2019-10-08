<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * System front navigation specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    // Homepage
    'nav-home' => [
        'order' => -100,
        'label' => _a('Home'),
        'route' => 'home',

        'pages' => [
            'account' => [
                'label'      => _a('Profile'),
                'route'      => 'sysuser',
                'controller' => 'profile',

                'pages' => [
                    'login' => [
                        'label'      => _a('Login'),
                        'route'      => 'sysuser',
                        'controller' => 'login',
                        'visible'    => 0,
                    ],

                    'register' => [
                        'label'      => _a('Register'),
                        'route'      => 'sysuser',
                        'controller' => 'register',
                        'visible'    => 0,
                    ],

                    'password' => [
                        'label'      => _a('Password'),
                        'route'      => 'sysuser',
                        'controller' => 'password',
                        'visible'    => 0,
                    ],
                ],
            ],
            'admin'   => [
                'label'   => _a('Admin'),
                'route'   => 'home',
                'section' => 'admin',
                'target'  => '_blank',
            ],
            'feed'    => [
                'label'   => _a('RSS Feed'),
                'route'   => 'feed',
                'section' => 'feed',
                'target'  => '_blank',
            ],
        ],
    ],

    'modules' => [
        'callback' => ['navigation', 'front'],
    ],
];
