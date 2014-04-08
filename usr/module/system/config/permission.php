<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
            'title'         => _t('Global public resource'),
            'access'        => array(
                'guest',
                'member',
            ),
        ),
        // global guest
        'guest' => array(
            'title'         => _t('Guest only'),
            'access'        => array(
                'guest',
            ),
        ),
        // global member
        'member'    => array(
            'title'         => _t('Member only'),
            'access'        => array(
                'member',
            ),
        ),
    ),
    // Admin section
    'admin' => array(
        // Generic admin resource
        'generic'   => array(
            'title'         => _t('Generic permission'),
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
            'title'         => _t('Operation: modules'),
            'access'        => array(
                'manager',
            ),
        ),
        // Themes
        'theme'    => array(
            'title'         => _t('Operation: themes'),
            'access'        => array(
                //'admin',
                'manager',
            ),
        ),
        // Navigation
        'navigation'    => array(
            'title'         => _t('Operation: navigation'),
            'access'        => array(
                //'admin',
                'manager',
            ),
        ),
        // Roles
        'role'    => array(
            'title'         => _t('Operation: roles'),
            'access'        => array(
                //'admin',
            ),
        ),
        /*
        // Users
        'user'    => array(
            'title'         => _t('Operation: users'),
            'access'        => array(
                //'admin',
            ),
        ),
        */
        // maintenance
        'maintenance'   => array(
            'title'         => _t('Operation: maintenance'),
            'access'        => array(
                //'admin',
                'manager',
            ),
        ),

        // Managed components
        // Configurations
        'config'    => array(
            'title'         => _t('Management: configs'),
            'access'        => array(
                'moderator',
                //'admin',
            ),
        ),
        // Block content and permission
        'block'     => array(
            'title'         => _t('Management: blocks'),
            'access'        => array(
                'moderator',
                //'admin',
            ),
        ),
        // Page dress up, cache and permission
        'page'     => array(
            'title'         => _t('Management: pages'),
            'access'        => array(
                'moderator',
                //'admin',
            ),
        ),
        // Caches
        'cache'  => array(
            'title'         => _t('Management: caches'),
            'access'        => array(
                //'admin',
            ),
        ),
        // Permissions
        'permission'  => array(
            'title'         => _t('Management: permissions'),
            'access'        => array(
                //'admin',
            ),
        ),
        // Event hooks
        'event'     => array(
            'title'         => _t('Management: events/hooks'),
            'access'        => array(
                //'admin',
                'moderator',
            ),
        ),
    ),
);
