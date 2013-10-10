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
        'users' => array(
            'label'         => _t('Users'),
            'permission'    => array(
                'resource'  => 'users',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'index',
            'action'        => 'index',
        ),

        'profile' => array(
            'label'         => _t('Profile field'),
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