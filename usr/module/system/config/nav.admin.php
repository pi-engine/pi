<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * System admin navigation specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    'modules' => [
        'label'      => _t('Modules'),
        'permission' => [
            'resource' => 'module',
        ],
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'module',
        //'action'        => 'index',

        'pages' => [
            'list'      => [
                'label'      => _t('Installed'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'module',
                'action'     => 'index',
                //'visible'       => 0,

                'pages' => [
                    'operation' => [
                        'route'      => 'admin',
                        'module'     => 'system',
                        'controller' => 'module',
                        'params'     => [
                            'from' => 'installed',
                        ],
                        'visible'    => 0,
                    ],
                ],
            ],
            'available' => [
                'label'      => _t('Availables'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'module',
                'action'     => 'available',

                'pages' => [
                    'operation' => [
                        'route'      => 'admin',
                        'module'     => 'system',
                        'controller' => 'module',
                        'params'     => [
                            'from' => 'available',
                        ],
                        'visible'    => 0,
                    ],
                ],
            ],
            'category'  => [
                'label'      => _t('Category'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'module',
                'action'     => 'category',
            ],
            'repo'      => [
                'label'      => _t('Repository'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'module',
                'action'     => 'repo',
                'visible'    => 0,
            ],
        ],
    ],

    'themes' => [
        'label'      => _t('Themes'),
        'permission' => [
            'resource' => 'theme',
        ],
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'theme',
        //'action'        => 'index',

        'pages' => [
            'apply'   => [
                'label'      => _t('In action'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'theme',
                'action'     => 'index',
                //'visible'       => 0,
            ],
            'list'    => [
                'label'      => _t('Installed'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'theme',
                'action'     => 'installed',
                //'visible'       => 0,
            ],
            'install' => [
                'label'      => _t('Availables'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'theme',
                'action'     => 'available',
                //'visible'       => 0,
            ],
            'repo'    => [
                'label'      => _t('Repository'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'theme',
                'action'     => 'repo',
                'visible'    => 0,
            ],
        ],

    ],

    'navigation' => [
        'label'      => _t('Navigation'),
        'permission' => [
            'resource' => 'navigation',
        ],
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'nav',
        //'action'        => 'index',

        'pages' => [
            'front' => [
                'label'      => _t('Navigation list'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'nav',
                'action'     => 'index',
                //'visible'       => 0,

                'pages' => [
                    'data' => [
                        'label'      => _t('Data manipulation'),
                        'route'      => 'admin',
                        'module'     => 'system',
                        'controller' => 'nav',
                        'action'     => 'data',
                        'visible'    => 0,
                    ],
                ],
            ],

            'add' => [
                'label'      => _t('Add'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'nav',
                'action'     => 'add',
                //'visible'       => 0,
            ],

            'select' => [
                'label'      => _t('Navigation setup'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'nav',
                'action'     => 'index',
                'visible'    => 0,
            ],
        ],
    ],

    'role' => [
        'label'      => _t('Role'),
        'permission' => [
            'resource' => 'role',
        ],
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'role',
        'action'     => 'index',

        'pages' => [
            'list' => [
                'label'      => _t('Role list'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'role',
                'action'     => '',
                'fragment'   => '!/all',
            ],
            'add'  => [
                'label'      => _t('Add role'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'role',
                'action'     => '',
                'fragment'   => '!/new',
            ],
        ],
    ],

    /*
    'user'  => array(
        'label'         => _t('Users'),
        'permission'    => array(
            'resource'  => 'user',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'user',

        'pages'         => array(
            'add'  => array(
                'label'         => _t('Add user'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'user',
                'action'        => 'add',
                'visible'       => 0,
            ),
            'edit'  => array(
                'label'         => _t('Edit user'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'user',
                'action'        => 'edit',
                'visible'       => 0,
            ),
            'password'  => array(
                'label'         => _t('Change password'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'user',
                'action'        => 'password',
                'visible'       => 0,
            ),
            'delete'  => array(
                'label'         => _t('Delete user'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'user',
                'action'        => 'delete',
                'visible'       => 0,
            ),
        ),
    ),
    */

    'asset' => [
        'label'      => _t('Asset publish'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'asset',
        'action'     => 'index',
        'permission' => [
            'resource' => 'maintenance',
        ],
    ],

    'flush' => [
        'label'      => _t('Cache flush'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'flush',
        'permission' => [
            'resource' => 'maintenance',
        ],
    ],

    'toolkit' => [
        'label'      => _t('Toolkit'),
        'permission' => [
            'resource' => 'maintenance',
        ],
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'audit',
        'action'     => 'index',

        'pages' => [
            'audit'   => [
                'label'      => _t('Audit'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'audit',
                'action'     => 'index',
                'visible'    => 0,
            ],
            'mailing' => [
                'label'      => _t('Mailing'),
                'route'      => 'admin',
                'module'     => 'system',
                'controller' => 'mail',
                'action'     => 'index',
                'visible'    => 0,
            ],
        ],
    ],

    'database' => [
        'label'      => _t('Database tools'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'database',
        'action'     => 'index',
        'permission' => [
            'resource' => 'maintenance',
        ],
    ],

    'layout' => [
        'label'      => _t('Homepage layout'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'page',
        'action'     => 'homepage',
        'permission' => [
            'resource' => 'maintenance',
        ],
    ],
];