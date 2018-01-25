<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Route specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    // default route
    'default' => [
        'name'     => 'default',
        'section'  => 'front',
        'priority' => -999,

        'type'    => 'Standard',
        'options' => [
            'prefix'              => '',
            'structure_delimiter' => '/',
            'param_delimiter'     => '/',
            'key_value_delimiter' => '-',
            'defaults'            => [
                'module'     => 'system',
                'controller' => 'index',
                'action'     => 'index',
            ],
        ],
    ],

    // Home route
    'home'    => [
        'name'     => 'home',
        'type'     => 'Home',
        'priority' => 10000,

        'options' => [
            'prefix'              => '',
            'structure_delimiter' => '-',
            'param_delimiter'     => '/',
            'key_value_delimiter' => '-',
        ],
    ],

    // admin route
    'admin'   => [
        'name'     => 'admin',
        'section'  => 'admin',
        'priority' => 100,

        'type'    => 'Standard',
        'options' => [
            'prefix' => '/admin',
        ],
    ],

    // API route
    'api'     => [
        'name'     => 'api',
        'section'  => 'api',
        'priority' => 100,

        'type'    => 'Api',
        'options' => [
            'prefix' => '/api',
        ],
    ],

    // feed route
    'feed'    => [
        'name'     => 'feed',
        'section'  => 'feed',
        'priority' => 100,

        'type'    => 'Feed',
        'options' => [
            'prefix' => '/feed',
        ],
    ],

    // System user route
    'sysuser' => [
        'name'     => 'sysuser',
        'type'     => 'Module\System\Route\User',
        'priority' => 5,
        'options'  => [
            'prefix' => '/system/user',
        ],
    ],
];
