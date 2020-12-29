<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Page setting specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    // Front section
    'front' => [
        // homepage
        [
            'title'      => _a('Homepage'),
            'module'     => 'system',
            'controller' => 'index',
            'action'     => 'index',
            'block'      => 1,
        ],
        // utility page, not used yet
        [
            'title'      => _a('Utility'),
            'module'     => 'system',
            'controller' => 'utility',
            'block'      => 1,
        ],
        // error message page
        [
            'title'      => _a('Error reporting'),
            'module'     => 'system',
            'controller' => 'error',
            'block'      => 0,
        ],
    ],
    // Admin section
    'admin' => [
        // System dashboard access
        [
            'title'      => _a('Dashboard'),
            'controller' => 'dashboard',
            //'permission'    => 'generic',
        ],
        // System readme
        [
            'title'      => _a('Readme'),
            'controller' => 'readme',
            //'permission'    => 'generic',
        ],

        // System managed components
        // config
        [
            'title'      => _a('Config'),
            'controller' => 'config',
            'permission' => 'config',
        ],
        // block
        [
            'title'      => _a('Blocks'),
            'controller' => 'block',
            'permission' => 'block',
        ],
        // page
        [
            'title'      => _a('Pages'),
            'controller' => 'page',
            'permission' => 'page',
        ],
        // event
        [
            'title'      => _a('Event/listener'),
            'controller' => 'event',
            'permission' => 'event',
        ],
        // Permissions
        [
            'title'      => _a('Permissions'),
            'controller' => 'perm',
            'permission' => 'permission',
        ],

        // Operations
        // module
        [
            'title'      => _a('Modules'),
            'controller' => 'module',
            'permission' => 'module',
        ],
        // theme
        [
            'title'      => _a('Themes'),
            'controller' => 'theme',
            'permission' => 'theme',
        ],
        // navigation
        [
            'title'      => _a('Navigation'),
            'controller' => 'nav',
            'permission' => 'navigation',
        ],

        // Role
        [
            'title'      => _a('Roles'),
            'controller' => 'role',
            'permission' => 'role',
        ],
        // User
        [
            'title'      => _a('User'),
            'controller' => 'user',
            'permission' => 'user',
        ],
        // Maintenance operations
        // asset
        [
            'title'      => _a('Asset'),
            'controller' => 'asset',
            'permission' => 'maintenance',
        ],
        // audit
        [
            'title'      => _a('Auditing'),
            'controller' => 'audit',
            'permission' => 'maintenance',
        ],
        // cache
        [
            'title'      => _a('Cache'),
            'controller' => 'cache',
            'permission' => 'maintenance',
        ],
    ],
    // Feed section
    'feed'  => [
        [
            'cache_ttl'   => 0,
            'cache_level' => '',
            'title'       => _a('What\'s new'),
        ],
    ],
];
