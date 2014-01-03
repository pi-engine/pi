<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

/**
 * Navigation config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'item'  => array(
        
        // Default admin navigation
        'admin'   => array(
            'list'              => array(
                'label'         => _t('List Media'),
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
                'label'         => _t('Application'),
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
                    'edit'              => array(
                        'label'         => _t('Edit'),
                        'route'         => 'admin',
                        'controller'    => 'application',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'analysis'          => array(
                'label'         => _t('Statistics'),
                'route'         => 'admin',
                'controller'    => 'analysis',
                'action'        => 'index',
                'permission'    => array(
                    'resource'  => 'analysis',
                ),
            ),
        ),
    ),
);
