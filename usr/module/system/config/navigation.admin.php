<?php
/**
 * System navigation config
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @version         $Id$
 */

return array(
    'modules' => array(
        'label'         => 'Modules',
        'resource'      => array(
            'resource'  => 'module',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'module',
        //'action'        => 'index',

        'pages'     => array(
            'list'  => array(
                'label'         => 'Installed',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'index',
                'visible'       => 0,
            ),
            'available' => array(
                'label'         => 'Availables',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'available',
                'visible'       => 0,
            ),
            'repo'  => array(
                'label'         => 'Repository',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'repo',
                'visible'       => 0,
            ),
        ),
    ),

    'themes'    => array(
        'label'         => 'Themes',
        'resource'      => array(
            'resource'  => 'theme',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'theme',
        //'action'        => 'index',

        'pages'     => array(
            'apply' => array(
                'label'         => 'In action',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'index',
                'visible'       => 0,
            ),
            'list' => array(
                'label'         => 'Installed',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'installed',
                'visible'       => 0,
            ),
            'install' => array(
                'label'         => 'Availables',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'available',
                'visible'       => 0,
            ),
            'repo' => array(
                'label'         => 'Repository',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'repo',
                'visible'       => 0,
            ),
        ),

    ),

    'navigations'   => array(
        'label'         => 'Navigations',
        'resource'      => array(
            'resource'  => 'navigation',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'nav',
        //'action'        => 'index',

        'pages' => array(
            'front' => array(
                'label'         => 'Navigations list',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'nav',
                'action'        => 'list',
                'visible'       => 0,

                'pages' => array(
                    'data'      => array(
                        'label'         => 'Data manipulation',
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'nav',
                        'action'        => 'data',
                        'visible'       => 0,
                    ),
                ),
            ),

            'select'    => array(
                'label'         => 'Navigation setup',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'nav',
                'action'        => 'index',
                'visible'       => 0,
            ),
        ),
    ),

    'role'   => array(
        'label'         => 'Roles',
        'resource'      => array(
            'resource'  => 'role',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'role',

        'pages'     => array(
            'front'      => array(
                'label'         => 'Front roles',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'role',
                'params'        => array(
                    'type'      => 'front',
                ),
                'visible'       => 0,
            ),
            'admin'  => array(
                'label'         => 'Admin roles',
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
        'label'         => 'Permissions',
        'resource'      => array(
            'resource'  => 'perm',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'perm',
        'action'        => 'front',

        'pages'     => array(
            'front'      => array(
                'label'         => 'Front resources',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'perm',
                'action'        => 'front',
                'visible'       => 0,
            ),
            'admin'  => array(
                'label'         => 'Admin resources',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'perm',
                'action'        => 'admin',
                'visible'       => 0,
            ),
        ),
    ),

    'members'   => array(
        'label'         => 'Membership',
        'resource'      => array(
            'resource'  => 'member',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'member',

        'pages'         => array(
            'add'  => array(
                'label'         => 'Add member',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'member',
                'action'        => 'add',
                'visible'       => 0,
            ),
            'edit'  => array(
                'label'         => 'Edit member',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'member',
                'action'        => 'edit',
                'visible'       => 0,
            ),
            'password'  => array(
                'label'         => 'Change password',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'member',
                'action'        => 'password',
                'visible'       => 0,
            ),
            'delete'  => array(
                'label'         => 'Delete member',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'member',
                'action'        => 'delete',
                'visible'       => 0,
            ),
        ),
    ),

    'toolkit'   => array(
        'label'         => 'Toolkit',
        'resource'      => array(
            'resource'  => 'maintenance',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'cache',
        'action'        => 'index',

        'pages'     => array(
            'cache'     => array(
                'label'         => 'Cache flush',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'cache',
                'action'        => 'index',
                'visible'       => 0,
            ),
            'asset'     => array(
                'label'         => 'Asset publish',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'asset',
                'action'        => 'index',
                'visible'       => 0,
            ),
            'audit'     => array(
                'label'         => 'Audit',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'audit',
                'action'        => 'index',
                'visible'       => 0,
            ),
            'mailing'   => array(
                'label'         => 'Mailing',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'mailing',
                'action'        => 'index',
                'visible'       => 0,
            ),
        ),
    ),
);
