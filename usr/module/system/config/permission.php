<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Permission specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    // Front section
    'front' => [
        // global public
        'public' => [
            'title'  => _t('Global public resource'),
            'access' => [
                'guest',
                'member',
            ],
        ],
        // global guest
        'guest'  => [
            'title'  => _t('Guest only'),
            'access' => [
                'guest',
            ],
        ],
        // global member
        'member' => [
            'title'  => _t('Member only'),
            'access' => [
                'member',
            ],
        ],
    ],
    // Admin section
    'admin' => [
        // Generic admin resource
        'generic'     => [
            'title'  => _t('Generic permission'),
            'access' => [
                //'admin',
                'staff',
                'moderator',
                'manager',
            ],
        ],

        // System operations
        // Modules
        'module'      => [
            'title'  => _t('Operation: modules'),
            'access' => [
                'manager',
            ],
        ],
        // Themes
        'theme'       => [
            'title'  => _t('Operation: themes'),
            'access' => [
                //'admin',
                'manager',
            ],
        ],
        // Navigation
        'navigation'  => [
            'title'  => _t('Operation: navigation'),
            'access' => [
                //'admin',
                'manager',
            ],
        ],
        // Roles
        'role'        => [
            'title'  => _t('Operation: roles'),
            'access' => [
                //'admin',
            ],
        ],
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
        'maintenance' => [
            'title'  => _t('Operation: maintenance'),
            'access' => [
                //'admin',
                'manager',
            ],
        ],

        // Managed components
        // Configurations
        'config'      => [
            'title'  => _t('Management: configs'),
            'access' => [
                'moderator',
                //'admin',
            ],
        ],
        // Block content and permission
        'block'       => [
            'title'  => _t('Management: blocks'),
            'access' => [
                'moderator',
                //'admin',
            ],
        ],
        // Page dress up, cache and permission
        'page'        => [
            'title'  => _t('Management: pages'),
            'access' => [
                'moderator',
                //'admin',
            ],
        ],
        // Caches
        'cache'       => [
            'title'  => _t('Management: caches'),
            'access' => [
                //'admin',
            ],
        ],
        // Permissions
        'permission'  => [
            'title'  => _t('Management: permissions'),
            'access' => [
                //'admin',
            ],
        ],
        // Event hooks
        'event'       => [
            'title'  => _t('Management: events/hooks'),
            'access' => [
                //'admin',
                'moderator',
            ],
        ],
    ],
];