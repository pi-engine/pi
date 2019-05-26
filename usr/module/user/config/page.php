<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    // Admin section
    'admin' => [
        [
            'controller' => 'index',
            'permission' => 'user',
        ],
        [
            'controller' => 'role',
            'permission' => 'role',
        ],
        [
            'controller' => 'profile',
            'permission' => 'profile',
        ],
        [
            'controller' => 'import',
            'permission' => 'import',
        ],
        [
            'controller' => 'avatar',
            'permission' => 'avatar',
        ],
        [
            'controller' => 'form',
            'permission' => 'form',
        ],
        [
            'controller' => 'plugin',
            'permission' => 'plugin',
        ],
        [
            'controller' => 'maintenance',
            'permission' => 'maintenance',
        ],
        [
            'controller' => 'inquiry',
            'permission' => 'inquiry',
        ],
        [
            'controller' => 'condition',
            'permission' => 'condition',
        ],
    ],
    // Front section
    'front' => [
        [
            'title'      => _a('Profile view'),
            'controller' => 'profile',
            'action'     => 'index',
            'permission' => 'profile-page',
            'block'      => 1,
        ],
        [
            'title'      => _a('Activities view'),
            'controller' => 'home',
            'action'     => 'index',
            'permission' => 'profile-page',
            'block'      => 1,
        ],
        [
            'title'      => _a('Dashboard view'),
            'controller' => 'dashboard',
            'action'     => 'index',
            'permission' => 'profile-page',
            'block'      => 1,
        ],
        [
            'title'      => _a('Dashboard pro view'),
            'controller' => 'dashboard-pro',
            'action'     => 'index',
            'permission' => 'profile-page',
            'block'      => 1,
        ],
    ],
];
