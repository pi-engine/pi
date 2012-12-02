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
    'dashboard'        => array(
        'label'         => 'Dashboard',
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'dashboard',
    ),

    'config'        => array(
        'label'         => 'Configurations',
        'resource'      => array(
            'resource'  => 'system-config',
            'privilege' => '',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'config',
        'action'        => 'index',
        'params'        => array(
            'category'  => 'general',
        ),

        'pages'     => array(
            /*
            'system'        => array(
                'label' => 'System Settings',

                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'config',
                'action'        => 'index',
                'params'        => array(
                    'category'  => 'general',
                ),

                'pages' => array(
                 */
                    'general'   => array(
                        'label'         => 'General',
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'config',
                        'action'        => 'index',
                        'params'        => array(
                            'category'  => 'general',
                        ),
                    ),
                    'meta'   => array(
                        'label'         => 'Head meta',
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'config',
                        'action'        => 'index',
                        'params'        => array(
                            'category'  => 'meta',
                        ),
                    ),
                    'mail'   => array(
                        'label'         => 'Mailing',
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'config',
                        'action'        => 'index',
                        'params'        => array(
                            'category'  => 'mail',
                        ),
                    ),
                    'text'   => array(
                        'label'         => 'Text processing',
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'config',
                        'action'        => 'index',
                        'params'        => array(
                            'category'  => 'text',
                        ),
                    ),
                    'user'   => array(
                        'label'         => 'User account',
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'config',
                        'action'        => 'index',
                        'params'        => array(
                            'category'  => 'user',
                        ),
                    ),
                /*
                ),
            ),
                 *
                 */


            'divider'   => array(),

            /* Module specific configurations
                /admin/preference/manage/dirname/moduleDirname
            */

            'modules' => array(
                /*
                'label'         => 'Module Configs',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'config',
                'action'        => 'module',
                */

                'callback'      => array('navigation', 'config'),
            ),
        ),
    ),

    'modules' => array(
        'label'         => 'Modules',
        'resource'      => array(
            'resource'  => 'system-module',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'module',
        'action'        => 'index',

        'pages'     => array(
            'list'  => array(
                'label'         => 'Installed',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'index',
            ),
            'available' => array(
                'label'         => 'Availables',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'available',
            ),
            'repo'  => array(
                'label'         => 'Repository',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'module',
                'action'        => 'repo',
                'visible'       => 0,
            ),
            'divider'   => array(),
            'entry'     => array(
                /*
                'label'         => 'Module entries',
                'uri'           => '',
                */
                // Pages will be generated by callback
                'callback'      => array('navigation', 'admin'),
            ),
        ),
    ),

    'content'    => array(
        'label'         => 'Content',
        'resource'      => array(
            'resource'  => 'system-content',
            'privilege' => '',
        ),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'block',
        'action'        => 'index',

        'pages'     => array(

            'block' => array(
                'label'         => 'Blocks',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'block',
                'action'        => 'index',

                'callback'      => array('navigation', 'block'),
            ),

            'page'      => array(
                'label'         => 'Pages',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'page',
                'action'        => 'index',
                'params'        => array(
                    'name'      => 'system',
                ),

                'callback'      => array('navigation', 'page'),
            ),

            'navigation'    => array(
                'label'         => 'Navigation',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'nav',
                'action'        => 'index',

                'pages' => array(
                    'front' => array(
                        'label'         => 'Front navigations',
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'nav',
                        'action'        => 'index',

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
                        'action'        => 'select',
                    ),
                ),
            ),
        ),
    ),

    'theme'     => array(
        'label'         => 'Themes',
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'theme',
        'action'        => 'index',

        'pages'     => array(
            'apply' => array(
                'label'         => 'In action',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'index',
            ),
            'list' => array(
                'label'         => 'Installed',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'installed',
            ),
            'install' => array(
                'label'         => 'Availables',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'theme',
                'action'        => 'available',
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

    'permission' => array(
        'label'         => 'Permissions',
        'resource'      => array(
            'resource'  => 'system-permission',
        ),
        'visible'       => 0,
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'role',
        'action'        => 'index',

        'pages'     => array(
            'role'      => array(
                'label'         => 'Roles',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'role',
                'action'        => 'index',
            ),
            'resource'  => array(
                'label'         => 'Resources',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'resource',
                'action'        => 'index',
            ),
            'rule'  => array(
                'label'         => 'Rules',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'rule',
                'action'        => 'index',
            ),
        ),
    ),

    'toolkit'   => array(
        'label'         => 'Toolkit',
        'resource'      => array(
            'resource'  => 'system-tookit',
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
            ),
            'asset'     => array(
                'label'         => 'Asset publish',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'asset',
                'action'        => 'index',
            ),
            'member'     => array(
                'label'         => 'Members',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'member',
                'action'        => 'index',
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
            'audit'     => array(
                'label'         => 'Audit Trail',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'audit',
                'action'        => 'index',
            ),
            'event'     => array(
                'label'         => 'Event hook',
                'route'         => 'admin',
                'module'        => 'system',
                'controller'    => 'event',
                'action'        => 'index',
                'pages'         => array(
                    'listener'  => array(
                        'label'         => 'Listeners',
                        'route'         => 'admin',
                        'module'        => 'system',
                        'controller'    => 'event',
                        'action'        => 'listener',
                        'visible'       => 0,
                    ),
                ),
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
