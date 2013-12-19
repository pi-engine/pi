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
            'list-all'          => array(
                'label'         => _t('List All'),
                'route'         => 'admin',
                'controller'    => 'list',
                'action'        => 'index',
                'permission'     => array(
                    'resource'  => 'list',
                ),
            ),
            'list-application'  => array(
                'label'         => _t('By Application'),
                'route'         => 'admin',
                'controller'    => 'list',
                'action'        => 'application',
                'permission'     => array(
                    'resource'  => 'list',
                ),
            ),
            'list-type'         => array(
                'label'         => _t('By Type'),
                'route'         => 'admin',
                'controller'    => 'list',
                'action'        => 'type',
                'permission'     => array(
                    'resource'  => 'list',
                ),
            ),
            'list-user'         => array(
                'label'         => _t('By User'),
                'route'         => 'admin',
                'controller'    => 'list',
                'action'        => 'user',
                'permission'     => array(
                    'resource'  => 'list',
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
            ),
            'analysis'          => array(
                'label'         => _t('Statistics'),
                'route'         => 'admin',
                'controller'    => 'analysis',
                'action'        => 'index',
                'permission'     => array(
                    'resource'  => 'analysis',
                ),
            ),
        ),
    ),
);
