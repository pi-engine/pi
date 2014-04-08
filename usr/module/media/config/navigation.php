<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Navigation config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'item'  => array(

        // Hide from front menu
        'front' => false,

        // Default admin navigation
        'admin'   => array(
            'list'              => array(
                'label'         => _t('Resource list'),
                'route'         => 'admin',
                'controller'    => 'list',
                'action'        => 'index',
                'permission'    => array(
                    'resource'  => 'list',
                ),
                
                'pages'         => array(
                    'all'               => array(
                        'label'         => _t('All'),
                        'route'         => 'admin',
                        'controller'    => 'list',
                        'action'        => 'index',
                    ),
                    'list-application'  => array(
                        'label'         => _t('By Application'),
                        'route'         => 'admin',
                        'controller'    => 'list',
                        'action'        => 'application',
                    ),
                    'type'              => array(
                        'label'         => _t('By Type'),
                        'route'         => 'admin',
                        'controller'    => 'list',
                        'action'        => 'type',
                    ),
                    'user'              => array(
                        'label'         => _t('By User'),
                        'route'         => 'admin',
                        'controller'    => 'list',
                        'action'        => 'user',
                    ),
                    'edit'              => array(
                        'label'         => _t('Edit'),
                        'route'         => 'admin',
                        'controller'    => 'media',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'application'       => array(
                'label'         => _t('Application list'),
                'route'         => 'admin',
                'controller'    => 'application',
                'action'        => 'list',
                'permission'    => array(
                    'resource'  => 'application',
                ),
                
                'pages'         => array(
                    'list'              => array(
                        'label'         => _t('List'),
                        'route'         => 'admin',
                        'controller'    => 'application',
                        'action'        => 'list',
                    ),
                    'add'               => array(
                        'label'         => _t('Add'),
                        'route'         => 'admin',
                        'controller'    => 'application',
                        'action'        => 'add',
                        'visible'       => 0,
                    ),
                    'edit'              => array(
                        'label'         => _t('Edit'),
                        'route'         => 'admin',
                        'controller'    => 'application',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'stats'             => array(
                'label'         => _t('Statistics'),
                'route'         => 'admin',
                'controller'    => 'stats',
                'action'        => 'index',
                'permission'    => array(
                    'resource'  => 'stats',
                ),
            ),
        ),
    ),
);
