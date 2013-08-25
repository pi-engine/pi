<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * System admin navigation specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'modules' => array(
        'label'         => _t('Modules'),
        'resource'      => array(
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
                'visible'       => 0,
            ),
            'available' => array(
                'label'         => _t('Availables'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'available',
                'visible'       => 0,
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
        'resource'      => array(
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
                'visible'       => 0,
            ),
            'list' => array(
                'label'         => _t('Installed'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'installed',
                'visible'       => 0,
            ),
            'install' => array(
                'label'         => _t('Availables'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'available',
                'visible'       => 0,
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

    'navigations'   => array(
        'label'         => _t('Navigations'),
        'resource'      => array(
            'resource'  => 'navigation',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'nav',
        //'action'        => 'index',

        'pages' => array(
            'front' => array(
                'label'         => _t('Navigations list'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'nav',
                'action'        => 'list',
                'visible'       => 0,

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
        'label'         => _t('Roles'),
        'resource'      => array(
            'resource'  => 'role',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'role',

        'pages'     => array(
            'front'      => array(
                'label'         => _t('Front roles'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'role',
                'params'        => array(
                    'type'      => 'front',
                ),
                'visible'       => 0,
            ),
            'admin'  => array(
                'label'         => _t('Admin roles'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'role',
                'params'        => array(
                    'type'      => 'admin',
                ),
                'visible'       => 0,
            ),
        ),
    ),

    'perm'   => array(
        'label'         => _t('Permissions'),
        'resource'      => array(
            'resource'  => 'perm',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'perm',
        'action'        => 'front',

        'pages'     => array(
            'front'      => array(
                'label'         => _t('Front resources'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'perm',
                'action'        => 'front',
                'visible'       => 0,
            ),
            'admin'  => array(
                'label'         => _t('Admin resources'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'perm',
                'action'        => 'admin',
                'visible'       => 0,
            ),
        ),
    ),

    'members'   => array(
        'label'         => _t('Membership'),
        'resource'      => array(
            'resource'  => 'member',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'member',

        'pages'         => array(
            'add'  => array(
                'label'         => _t('Add member'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'member',
                'action'        => 'add',
                'visible'       => 0,
            ),
            'edit'  => array(
                'label'         => _t('Edit member'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'member',
                'action'        => 'edit',
                'visible'       => 0,
            ),
            'password'  => array(
                'label'         => _t('Change password'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'member',
                'action'        => 'password',
                'visible'       => 0,
            ),
            'delete'  => array(
                'label'         => _t('Delete member'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'member',
                'action'        => 'delete',
                'visible'       => 0,
            ),
        ),
    ),

    'toolkit'   => array(
        'label'         => _t('Toolkit'),
        'resource'      => array(
            'resource'  => 'maintenance',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'cache',
        'action'        => 'index',

        'pages'     => array(
            'cache'     => array(
                'label'         => _t('Cache flush'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'cache',
                'action'        => 'index',
                'visible'       => 0,
            ),
            'asset'     => array(
                'label'         => _t('Asset publish'),
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'asset',
                'action'        => 'index',
                'visible'       => 0,
            ),
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
                'controller'    => 'mailing',
                'action'        => 'index',
                'visible'       => 0,
            ),
        ),
    ),
);
