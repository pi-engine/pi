<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * System admin navigation specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'modules' => array(
        'label'         => _t('Modules'),
        'permission'    => array(
            'resource'  => 'module',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'module',
        //'action'        => 'index',

        'pages'     => array(
            'list'  => array(
                'label'         => _t('Installed'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'index',
                //'visible'       => 0,

                'pages'         => array(
                    'operation' => array(
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'module',
                        'params'        => array(
                            'from'      => 'installed',
                        ),
                        'visible'       => 0,
                    ),
                ),
            ),
            'available' => array(
                'label'         => _t('Availables'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'available',

                'pages'         => array(
                    'operation' => array(
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'module',
                        'params'        => array(
                            'from'      => 'available',
                        ),
                        'visible'       => 0,
                    ),
                ),
            ),
            'category'  => array(
                'label'         => _t('Category'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'category',
            ),
            'repo'  => array(
                'label'         => _t('Repository'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'repo',
                'visible'       => 0,
            ),
        ),
    ),

    'themes'    => array(
        'label'         => _t('Themes'),
        'permission'    => array(
            'resource'  => 'theme',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'theme',
        //'action'        => 'index',

        'pages'     => array(
            'apply' => array(
                'label'         => _t('In action'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'index',
                //'visible'       => 0,
            ),
            'list' => array(
                'label'         => _t('Installed'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'installed',
                //'visible'       => 0,
            ),
            'install' => array(
                'label'         => _t('Availables'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'available',
                //'visible'       => 0,
            ),
            'repo' => array(
                'label'         => _t('Repository'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'repo',
                'visible'       => 0,
            ),
        ),

    ),

    'navigation'    => array(
        'label'         => _t('Navigation'),
        'permission'    => array(
            'resource'  => 'navigation',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'nav',
        //'action'        => 'index',

        'pages' => array(
            'front' => array(
                'label'         => _t('Navigation list'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'nav',
                'action'        => 'index',
                //'visible'       => 0,

                'pages' => array(
                    'data'      => array(
                        'label'         => _t('Data manipulation'),
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'nav',
                        'action'        => 'data',
                        'visible'       => 0,
                    ),
                ),
            ),

            'add'    => array(
                'label'         => _t('Add'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'nav',
                'action'        => 'add',
                //'visible'       => 0,
            ),

            'select'    => array(
                'label'         => _t('Navigation setup'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'nav',
                'action'        => 'index',
                'visible'       => 0,
            ),
        ),
    ),

    'role'   => array(
        'label'         => _t('Role'),
        'permission'    => array(
            'resource'  => 'role',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'role',
        'action'        => 'index',

        'pages'     => array(
            'list'      => array(
                'label'         => _t('Role list'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'role',
                'action'        => '',
                'fragment'      => '!/all',
            ),
            'add'      => array(
                'label'         => _t('Add role'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'role',
                'action'        => '',
                'fragment'      => '!/new',
            ),
        ),
    ),

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

    'flush'     => array(
        'label'         => _t('Cache flush'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'flush',
        'permission'    => array(
            'resource'  => 'maintenance',
        ),
    ),

    'asset'     => array(
        'label'         => _t('Asset publish'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'asset',
        'action'        => 'index',
        'permission'    => array(
            'resource'  => 'maintenance',
        ),
    ),

    'toolkit'   => array(
        'label'         => _t('Toolkit'),
        'permission'    => array(
            'resource'  => 'maintenance',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'audit',
        'action'        => 'index',

        'pages'     => array(
            'audit'     => array(
                'label'         => _t('Audit'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'audit',
                'action'        => 'index',
                'visible'       => 0,
            ),
            'mailing'   => array(
                'label'         => _t('Mailing'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'mail',
                'action'        => 'index',
                'visible'       => 0,
            ),
        ),
    ),
);
