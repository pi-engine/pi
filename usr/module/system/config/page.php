<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Page setting specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Front section
    'front' => array(
        // homepage
        array(
            'title'         => _a('Homepage'),
            'module'        => 'system',
            'controller'    => 'index',
            'action'        => 'index',
            'block'         => 1,
        ),
        // utility page, not used yet
        array(
            'title'         => _a('Utility'),
            'module'        => 'system',
            'controller'    => 'utility',
            'block'         => 1,
        ),
        // error message page
        array(
            'title'         => _a('Error reporting'),
            'module'        => 'system',
            'controller'    => 'error',
            'block'         => 0,
        ),
    ),
    // Admin section
    'admin' => array(
        // System dashboard access
        array(
            'title'         => _a('Dashboard'),
            'controller'    => 'dashboard',
            //'permission'    => 'generic',
        ),
        // System readme
        array(
            'title'         => _a('Readme'),
            'controller'    => 'readme',
            //'permission'    => 'generic',
        ),

        // System managed components
        // config
        array(
            'title'         => _a('Config'),
            'controller'    => 'config',
            'permission'    => 'config',
        ),
        // block
        array(
            'title'         => _a('Blocks'),
            'controller'    => 'block',
            'permission'    => 'block',
        ),
        // page
        array(
            'title'         => _a('Pages'),
            'controller'    => 'page',
            'permission'    => 'page',
        ),
        // event
        array(
            'title'         => _a('Event/listener'),
            'controller'    => 'event',
            'permission'    => 'event',
        ),
        // Permissions
        array(
            'title'         => _a('Permissions'),
            'controller'    => 'perm',
            'permission'    => 'permission',
        ),

        // Operations
        // module
        array(
            'title'         => _a('Modules'),
            'controller'    => 'module',
            'permission'    => 'module',
        ),
        // theme
        array(
            'title'         => _a('Themes'),
            'controller'    => 'theme',
            'permission'    => 'theme',
        ),
        // navigation
        array(
            'title'         => _a('Navigation'),
            'controller'    => 'nav',
            'permission'    => 'navigation',
        ),

        // Role
        array(
            'title'         => _a('Roles'),
            'controller'    => 'role',
            'permission'    => 'role',
        ),
        // User
        array(
            'title'         => _a('User'),
            'controller'    => 'user',
            'permission'    => 'user',
        ),
        // Maintenance operations
        // asset
        array(
            'title'         => _a('Asset'),
            'controller'    => 'asset',
            'permission'    => 'maintenance',
        ),
        // audit
        array(
            'title'         => _a('Auditing'),
            'controller'    => 'audit',
            'permission'    => 'maintenance',
        ),
        // cache
        array(
            'title'         => _a('Cache'),
            'controller'    => 'cache',
            'permission'    => 'maintenance',
        ),
    ),
    // Feed section
    'feed' => array(
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => _a('What\'s new'),
        ),
    ),
);
