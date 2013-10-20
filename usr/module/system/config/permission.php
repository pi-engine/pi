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
        'generic'   => array(
            'title'         => __('Generic permission'),
            'access'        => array(
                //'admin',
                'staff',
                'moderator',
                'manager',
            ),
        ),

        // System operations
        // Modules
        'module'    => array(
            'title'         => __('Operation: modules'),
            'access'        => array(
                'manager',
            ),
        ),
        // Themes
        'theme'    => array(
            'title'         => __('Operation: themes'),
            'access'        => array(
                //'admin',
                'manager',
            ),
        ),
        // Navigation
        'navigation'    => array(
            'title'         => __('Operation: navigation'),
            'access'        => array(
                //'admin',
                'manager',
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

        // Managed components
        // Configurations
        'config'    => array(
            'title'         => __('Management: configs'),
            'access'        => array(
                'moderator',
                //'admin',
            ),
        ),
        // Block content and permission
        'block'     => array(
            'title'         => __('Management: blocks'),
            'access'        => array(
                'moderator',
                //'admin',
            ),
        ),
        // Page dress up, cache and permission
        'page'     => array(
            'title'         => __('Management: pages'),
            'access'        => array(
                'moderator',
                //'admin',
            ),
        ),
        // Permissions
        'permission'  => array(
            'title'         => __('Management: permissions'),
            'access'        => array(
                //'admin',
            ),
        ),
        // Event hooks
        'event'     => array(
            'title'         => __('Management: events/hooks'),
            'access'        => array(
                //'admin',
                'moderator',
            ),
        ),
    ),
);
