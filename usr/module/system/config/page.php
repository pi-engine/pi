<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
            'title'         => __('Homepage'),
            'module'        => 'system',
            'controller'    => 'index',
            'action'        => 'index',
            'block'         => 1,
        ),
        // utility page, not used yet
        array(
            'title'         => __('Utility'),
            'module'        => 'system',
            'controller'    => 'utility',
            'block'         => 1,
        ),
        // error message page
        array(
            'title'         => __('Error reporting'),
            'module'        => 'system',
            'controller'    => 'error',
            'block'         => 0,
        ),
    ),
    // Admin section
    'admin' => array(
        // System dashboard access
        array(
            'controller'    => 'dashboard',
            'permission'    => 'admin',
        ),
        // System readme
        array(
            'controller'    => 'readme',
            'permission'    => 'admin',
        ),

        // System managed components
        // config
        array(
            'controller'    => 'config',
            'permission'    => 'config',
        ),
        // block
        array(
            'controller'    => 'block',
            'permission'    => 'block',
        ),
        // page
        array(
            'controller'    => 'page',
            'permission'    => 'page',
        ),
        // event
        array(
            'controller'    => 'event',
            'permission'    => 'event',
        ),
        // resource permissions
        array(
            'controller'    => 'resource',
            'permission'    => 'resource',
        ),

        // Operations
        // module
        array(
            'controller'    => 'module',
            'permission'    => 'module',
        ),
        // theme
        array(
            'controller'    => 'theme',
            'permission'    => 'theme',
        ),
        // navigation
        array(
            'controller'    => 'nav',
            'permission'    => 'navigation',
        ),
        // System permissions
        array(
            'controller'    => 'perm',
            //'action'        => 'index',
            'permission'    => 'perm',
        ),

        // role
        array(
            'controller'    => 'role',
            'permission'    => 'role',
        ),
        // membership
        array(
            'controller'    => 'member',
            'permission'    => 'member',
        ),
        // Maintenance operations
        // asset
        array(
            'controller'    => 'asset',
            'permission'    => 'maintenance',
        ),
        // audit
        array(
            'controller'    => 'audit',
            'permission'    => 'maintenance',
        ),
        // cache
        array(
            'controller'    => 'cache',
            'permission'    => 'maintenance',
        ),
    ),
    // Feed section
    'feed' => array(
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => __('What\'s new'),
        ),
    ),

    /*
    // Exception of admin pages to skip ACL check
    'exception' => array(
        array(
            'controller'    => 'resource',
            'action'        => 'assign',
        ),
        array(
            'controller'    => 'perm',
            'action'        => 'assign',
        ),
        array(
            'controller'    => 'block',
            'action'        => 'page',
        ),
    ),
    */
);
