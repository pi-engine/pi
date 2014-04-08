<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * User navigation specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

return array(
    'front'   => false,
    'admin' => array(
        'user'  => array(
            'label'         => _t('User'),
            'permission'    => array(
                'resource'  => 'user',
            ),
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'index',
        ),

        'role'   => array(
            'label'         => _t('Role'),
            'permission'    => array(
                'resource'  => 'role',
            ),
            'route'         => 'admin',
            'controller'    => 'role',

            'pages'     => array(
                'front'      => array(
                    'label'         => _t('Front role'),
                    'route'         => 'admin',
                    'controller'    => 'role',
                    'params'        => array(
                        'type'      => 'front',
                    ),
                    'visible'       => 0,
                ),
                'admin'  => array(
                    'label'         => _t('Admin role'),
                    'route'         => 'admin',
                    'controller'    => 'role',
                    'params'        => array(
                        'type'      => 'admin',
                    ),
                    'visible'       => 0,
                ),
            ),
        ),
    ),
);