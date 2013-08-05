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
            'permission'    => array(
                // Defined in acl.php
                'parent'        => 'admin',
            ),
        ),
        // System readme
        array(
            'controller'    => 'readme',
            'permission'    => array(
                // Defined in acl.php
                'parent'        => 'admin',
            ),
        ),

        // System managed components
        // config
        array(
            'controller'    => 'config',
            'permission'    => array(
                // Defined in acl.php
                'parent'        => 'config',
            ),
        ),
        // block
        array(
            'controller'    => 'block',
            'permission'    => array(
                'parent'        => 'block',
            ),
        ),
        // page
        array(
            'controller'    => 'page',
            'permission'    => array(
                'parent'        => 'page',
            ),
        ),
        // event
        array(
            'controller'    => 'event',
            'permission'    => array(
                'parent'        => 'event',
            ),
        ),
        // resource permissions
        array(
            'controller'    => 'resource',
            'permission'    => array(
                'parent'        => 'resource',
            ),
        ),

        // Operations
        // module
        array(
            'controller'    => 'module',
            'permission'    => array(
                'parent'        => 'module',
            ),
        ),
        // theme
        array(
            'controller'    => 'theme',
            'permission'    => array(
                'parent'        => 'theme',
            ),
        ),
        // navigation
        array(
            'controller'    => 'nav',
            'permission'    => array(
                'parent'        => 'navigation',
            ),
        ),
        // System permissions
        array(
            'controller'    => 'perm',
            //'action'        => 'index',
            'permission'    => array(
                'parent'        => 'perm',
            ),
        ),

        // role
        array(
            //'parent'        => 'member',
            'controller'    => 'role',
            'permission'    => array(
                'parent'        => 'role',
            ),
        ),
        // membership
        array(
            //'parent'        => 'member',
            'controller'    => 'member',
            'permission'    => array(
                'parent'        => 'member',
            ),
        ),
        // Maintenance operaions
        // asset
        array(
            'controller'    => 'asset',
            'permission'    => array(
                'parent'        => 'maintenance',
            ),
        ),
        // audit
        array(
            'controller'    => 'audit',
            'permission'    => array(
                'parent'        => 'maintenance',
            ),
        ),
        // cache
        array(
            'controller'    => 'cache',
            'permission'    => array(
                'parent'        => 'maintenance',
            ),
        ),
    ),
    // Feed section
    'feed' => array(
        array(
            'cache_expire'  => 0,
            'cache_level'   => '',
            'title'         => __('What\'s new'),
        ),
    ),

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
);
