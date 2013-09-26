<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Permission specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Front section
    'front' => array(
        // global public
        'public'    => array(
            'title'         => __('Global public resource'),
            'access'        => array(
                'guest',
                'member',
            ),
        ),
        // global guest
        'guest' => array(
            'title'         => __('Guest only'),
            'access'        => array(
                'guest',
            ),
        ),
        // global member
        'member'    => array(
            'title'         => __('Member only'),
            'access'        => array(
                'member',
            ),
        ),
    ),
    // Admin section
    'admin' => array(
        // Generic admin resource
        'admin'     => array(
            'title'         => __('Global admin permission'),
            'access'        => array(
                //'admin',
                'staff',
            ),
        ),

        // Managed components
        // Configurations
        'config'    => array(
            'title'         => __('Component: configurations'),
            'access'        => array(
                'moderator',
                //'admin',
            ),
        ),
        // Block content and permission
        'block'     => array(
            'title'         => __('Component: blocks'),
            'access'        => array(
                'moderator',
                //'admin',
            ),
        ),
        // Page dress up, cache and permission
        'page'     => array(
            'title'         => __('Component: pages'),
            'access'        => array(
                'moderator',
                //'admin',
            ),
        ),
        // Permissions
        'permission'  => array(
            'title'         => __('Component: permissions'),
            'access'        => array(
                //'admin',
            ),
        ),
        // Event hooks
        'event'     => array(
            'title'         => __('Component: events/hooks'),
            'access'        => array(
                //'admin',
                'moderator',
            ),
        ),

        // System operations
        // Modules
        'module'    => array(
            'title'         => __('Operation: modules'),
            'access'        => array(
                'manager',
                'webmaster',
            ),
        ),
        // Themes
        'theme'    => array(
            'title'         => __('Operation: themes'),
            'access'        => array(
                //'admin',
                'editor',
            ),
        ),
        // Navigation
        'navigation'    => array(
            'title'         => __('Operation: navigation'),
            'access'        => array(
                //'admin',
                'editor',
            ),
        ),
        // Roles
        'role'    => array(
            'title'         => __('Operation: roles'),
            'access'        => array(
                //'admin',
            ),
        ),
        // Users
        'user'    => array(
            'title'         => __('Operation: users'),
            'access'        => array(
                //'admin',
            ),
        ),
        // maintenance
        'maintenance'   => array(
            'title'         => __('Operation: maintenance'),
            'access'        => array(
                //'admin',
                'manager',
            ),
        ),
    ),
);
