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
            'resource'      => array(
                'resource'  => 'module',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'index',
            'action'        => 'index',
        ),

        'profile' => array(
            'label'         => _t('Profile field'),
            'resource'      => array(
                'resource'  => 'module',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'profile',
            'action'        => 'index',
        ),

        'avatar' => array(
            'label'         => _t('Avatar'),
            'resource'      => array(
                'resource'  => 'module',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'avatar',
            'action'        => 'index',
        ),

        'form' => array(
            'label'         => _t('Form'),
            'resource'      => array(
                'resource'  => 'module',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'form',
            'action'        => 'index',
        ),

        'plugin' => array(
            'label'         => _t('Plugin management'),
            'resource'      => array(
                'resource'  => 'module',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'plugin',
            'action'        => 'index',
        ),

        'notification' => array(
            'label'         => _t('Notification'),
            'resource'      => array(
                'resource'  => 'module',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'notification',
            'action'        => 'index',
        ),

        'static' => array(
            'label'         => _t('Static'),
            'resource'      => array(
                'resource'  => 'module',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'static',
            'action'        => 'index',
        ),
    ),
);