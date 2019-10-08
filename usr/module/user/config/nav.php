<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * User navigation specs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

return [
    'front' => [
    ],
    'admin' => [
        'user' => [
            'label'      => _t('User'),
            'permission' => [
                'resource' => 'user',
            ],
            'route'      => 'admin',
            'module'     => 'user',
            'controller' => 'index',
            'action'     => 'index',

            'pages' => [
                'edit'      => [
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'edit',
                    'visible'    => 0,
                ],
                'view'      => [
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'view',
                    'visible'    => 0,
                ],
                'all'       => [
                    'label'      => _t('All'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'index',
                    'action'     => '',
                    'fragment'   => '!/all',
                ],
                'activated' => [
                    'label'      => _t('Activated user'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'index',
                    'action'     => '',
                    'fragment'   => '!/activated',
                ],
                'pending'   => [
                    'label'      => _t('Pending user'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'index',
                    'action'     => '',
                    'fragment'   => '!/pending',
                ],
                'new'       => [
                    'label'      => _t('Add user'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'index',
                    'action'     => '',
                    'fragment'   => '!/new',
                ],
                'search'    => [
                    'label'      => _t('Advanced search'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'index',
                    'action'     => '',
                    'fragment'   => '!/search',
                ],
            ],
        ],

        'role' => [
            'label'      => _t('Role'),
            'permission' => [
                'resource' => 'role',
            ],
            'route'      => 'admin',
            'module'     => 'user',
            'controller' => 'role',
        ],

        'profile' => [
            'label'      => _t('Profile'),
            'permission' => [
                'resource' => 'profile',
            ],
            'route'      => 'admin',
            'module'     => 'user',
            'controller' => 'profile',
            'action'     => 'index',

            'pages' => [
                'field'   => [
                    'label'      => _t('Profile field'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'profile',
                    'action'     => '',
                    'fragment'   => '!/field',
                ],
                'dress'   => [
                    'label'      => _t('Profile dress up'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'profile',
                    'action'     => '',
                    'fragment'   => '!/dress',
                ],
                'privacy' => [
                    'label'      => _t('Field privacy'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'profile',
                    'action'     => '',
                    'fragment'   => '!/privacy',
                ],
            ],
        ],

        'form' => [
            'label'      => _t('Form'),
            'permission' => [
                'resource' => 'form',
            ],
            'route'      => 'admin',
            'module'     => 'user',
            'controller' => 'form',
            'action'     => 'index',
            'visible'    => 0,
        ],

        'plugin' => [
            'label'      => _t('Plugin management'),
            'permission' => [
                'resource' => 'plugin',
            ],
            'route'      => 'admin',
            'module'     => 'user',
            'controller' => 'plugin',
            'action'     => 'index',

            'pages' => [
                'timeline'  => [
                    'label'      => _t('Timeline'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'plugin',
                    'action'     => '',
                    'fragment'   => '!/timeline',
                ],
                'activity'  => [
                    'label'      => _t('Activity'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'plugin',
                    'action'     => '',
                    'fragment'   => '!/activity',
                ],
                'quicklink' => [
                    'label'      => _t('Quicklink'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'plugin',
                    'action'     => '',
                    'fragment'   => '!/quicklink',
                ],
            ],
        ],

        'maintenance' => [
            'label'      => _t('Maintenance'),
            'permission' => [
                'resource' => 'maintenance',
            ],
            'route'      => 'admin',
            'module'     => 'user',
            'controller' => 'maintenance',
            'action'     => 'index',

            'pages' => [
                'stats'         => [
                    'label'      => _t('Stats'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'maintenance',
                    'action'     => '',
                    'fragment'   => '!/stats',
                ],
                'logs'          => [
                    'label'      => _t('User log'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'maintenance',
                    'action'     => '',
                    'fragment'   => '!/logs',
                ],
                'timeline_logs' => [
                    'label'      => _t('Timeline log'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'maintenance',
                    'action'     => '',
                    'fragment'   => '!/timeline',
                ],
                'deleted'       => [
                    'label'      => _t('Deleted users'),
                    'route'      => 'admin',
                    'module'     => 'user',
                    'controller' => 'maintenance',
                    'action'     => '',
                    'fragment'   => '!/deleted',
                ],
            ],
        ],

        'inquiry' => [
            'label'      => _t('Inquiry'),
            'route'      => 'admin',
            'module'     => 'user',
            'controller' => 'inquiry',
            'action'     => 'index',
        ],

        'condition' => [
            'label'      => _t('Terms and conditions'),
            'permission' => [
                'resource' => 'condition',
            ],
            'route'      => 'admin',
            'module'     => 'user',
            'controller' => 'condition',
            'action'     => 'index',
        ],

        /*
        'import'  => array(
            'label'         => _t('Import'),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'import',
        ),
        */
    ],
];