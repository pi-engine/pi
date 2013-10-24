<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User navigation specs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

return array(
    'front'   => array(
    ),
    'admin' => array(
        'user'  => array(
            'label'         => _t('User'),
            'permission'    => array(
                'resource'  => 'account',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'index',
            'action'        => 'index',
        ),

        'role'   => array(
            'label'         => _t('Role'),
            'permission'    => array(
                'resource'  => 'role',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'role',

            'pages'     => array(
                'front'      => array(
                    'label'         => _t('Front role'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'role',
                    'params'        => array(
                        'type'      => 'front',
                    ),
                    'visible'       => 0,
                ),
                'admin'  => array(
                    'label'         => _t('Admin role'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'role',
                    'params'        => array(
                        'type'      => 'admin',
                    ),
                    'visible'       => 0,
                ),
            ),
        ),

        'profile' => array(
            'label'         => _t('Profile'),
            'permission'    => array(
                'resource'  => 'profile',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'profile',
            'action'        => 'index',
        ),

        'form' => array(
            'label'         => _t('Form'),
            'permission'    => array(
                'resource'  => 'form',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'form',
            'action'        => 'index',
        ),

        'plugin' => array(
            'label'         => _t('Plugin management'),
            'permission'    => array(
                'resource'  => 'plugin',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'plugin',
            'action'        => 'index',
        ),

        'maintenance' => array(
            'label'         => _t('Maintenance'),
            'permission'    => array(
                'resource'  => 'maintenance',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'maintenance',
            'action'        => 'index',
        ),
    ),
);