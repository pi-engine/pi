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
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

return array(
    'front'   => array(
    ),
    'admin' => array(
        'user'  => array(
            'label'         => _t('User'),
            'permission'    => array(
                'resource'  => 'user',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'index',
            'action'        => 'index',

            'pages'     => array(
                'edit'      => array(
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'edit',
                    'visible'       => 0,
                ),
                'all'      => array(
                    'label'         => _t('All'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'index',
                    'action'        => '',
                    'fragment'      => '!/all'
                ),
                'activated'      => array(
                    'label'         => _t('Activated user'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'index',
                    'action'        => '',
                    'fragment'      => '!/activated'
                ),
                'pending'      => array(
                    'label'         => _t('Pending user'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'index',
                    'action'        => '',
                    'fragment'      => '!/pending'
                ),
                'new'         => array(
                    'label'         => _t('Add user'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'index',
                    'action'        => '',
                    'fragment'      => '!/new'
                ),
                'search'      => array(
                    'label'         => _t('Advanced search'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'index',
                    'action'        => '',
                    'fragment'      => '!/search'
                ),      
            ),
        ),

        'role'   => array(
            'label'         => _t('Role'),
            'permission'    => array(
                'resource'  => 'role',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'role',
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

            'pages'         => array(
                'field'      => array(
                    'label'         => _t('Profile field'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'profile',
                    'action'        => '',
                    'fragment'      => '!/field'
                ),
                'dress'      => array(
                    'label'         => _t('Profile dress up'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'profile',
                    'action'        => '',
                    'fragment'      => '!/dress'
                ),
                'privacy'      => array(
                    'label'         => _t('Field privacy'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'profile',
                    'action'        => '',
                    'fragment'      => '!/privacy'
                ),
            ),
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
            'visible'       => 0,
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

            'pages'         => array(
                'timeline'      => array(
                    'label'         => _t('Timeline'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'plugin',
                    'action'        => '',
                    'fragment'      => '!/timeline'
                ),
                'activity'      => array(
                    'label'         => _t('Activity'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'plugin',
                    'action'        => '',
                    'fragment'      => '!/activity'
                ),
                'quicklink'      => array(
                    'label'         => _t('Quicklink'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'plugin',
                    'action'        => '',
                    'fragment'      => '!/quicklink'
                ),
            ),
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

            'pages'         => array(
                'stats'      => array(
                    'label'         => _t('Stats'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'maintenance',
                    'action'        => '',
                    'fragment'      => '!/stats'
                ),
                'logs'      => array(
                    'label'         => _t('User log'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'maintenance',
                    'action'        => '',
                    'fragment'      => '!/logs'
                ),
                'deleted'      => array(
                    'label'         => _t('Deleted users'),
                    'route'         => 'admin',
                    'module'        => 'user',
                    'controller'    => 'maintenance',
                    'action'        => '',
                    'fragment'      => '!/deleted'
                ),
            ),
        ),

        'inquiry'  => array(
            'label'         => _t('Inquiry'),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'inquiry',
            'action'        => 'index',
        ),

        /*
        'import'  => array(
            'label'         => _t('Import'),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'import',
        ),
        */
    ),
);