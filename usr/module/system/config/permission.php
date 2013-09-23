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
            'module'        => 'system',
            'title'         => __('Global public resource'),
            'access'        => array(
                'guest'     => 1,
                'member'    => 1,
            ),
        ),
        // global guest
        'guest' => array(
            'module'        => 'system',
            'title'         => __('Guest only'),
            'access'        => array(
                'guest'     => 1,
            ),
        ),
        // global member
        'member'    => array(
            'module'        => 'system',
            'title'         => __('Member only'),
            'access'        => array(
                'member'    => 1,
            ),
        ),
    ),
    // Admin section
    'admin' => array(
        // Generic admin resource
        'admin'     => array(
            'title'         => __('Global admin permission'),
            'access'        => array(
                'admin'     => 1,
                'staff'     => 1,
            ),
        ),

        // Managed components
        // Configurations
        'config'    => array(
            'title'         => __('Component: configurations'),
            'access'        => array(
                'moderator' => 1,
                'admin'     => 1,
            ),
        ),
        // Block content and permission
        'block'     => array(
            'title'         => __('Component: blocks'),
            'access'        => array(
                'moderator' => 1,
                'admin'     => 1,
            ),
        ),
        // Page dress up, cache and permission
        'page'     => array(
            'title'         => __('Component: pages'),
            'access'        => array(
                'moderator' => 1,
                'admin'     => 1,
            ),
        ),
        // Resource permissions
        'resource'  => array(
            'title'         => __('Component: resources'),
            'access'        => array(
                'admin'     => 1,
            ),
        ),
        // Event hooks
        'event'     => array(
            'title'         => __('Component: events/hooks'),
            'access'        => array(
                'admin'     => 1,
                'moderator' => 1,
            ),
        ),

        // System operations
        // Modules
        'module'    => array(
            'title'         => __('Operation: modules'),
            'access'        => array(
                'staff'     => 0,
                'manager'   => 1,
                'webmaster' => 1,
            ),
        ),
        // Themes
        'theme'    => array(
            'title'         => __('Operation: themes'),
            'access'        => array(
                'admin'     => 1,
                'editor'    => 1,
            ),
        ),
        // Navigations
        'navigation'    => array(
            'title'         => __('Operation: navigatons'),
            'access'        => array(
                'admin'     => 1,
                'editor'    => 1,
            ),
        ),
        // Roles
        'role'    => array(
            'title'         => __('Operation: roles'),
            'access'        => array(
                'admin'     => 1,
            ),
        ),
        // Permissions
        'perm'    => array(
            'title'         => __('Operation: permissions'),
            'access'        => array(
                'admin'     => 1,
            ),
        ),
        // Members
        'member'    => array(
            'title'         => __('Operation: members'),
            'access'        => array(
                'admin'     => 1,
            ),
        ),
        // maintenance
        'maintenance'   => array(
            'title'         => __('Operation: maintenance'),
            'access'        => array(
                'admin'     => 1,
                'manager'   => 1,
            ),
        ),
    ),
);
