<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    'item'  => array(
        'front'     => false,
        'admin'     => array(
            'script'     => array(
                'label'         => 'Script widgets',
                'route'         => 'admin',
                'controller'    => 'index',
                'action'        => 'index',
                'resource'      => array(
                    'resource'  => 'script',
                ),
            ),
            'static'     => array(
                'label'         => 'Static widgets',
                'route'         => 'admin',
                'controller'    => 'static',
                'action'        => 'index',
                'resource'      => array(
                    'resource'  => 'static',
                ),

                'pages'         => array(
                    'add'   => array(
                        'label'         => 'Add',
                        'route'         => 'admin',
                        'controller'    => 'static',
                        'action'        => 'add',
                        'visible'       => 0,
                    ),
                    'edit'   => array(
                        'label'         => 'Edit',
                        'route'         => 'admin',
                        'controller'    => 'static',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'carousel'     => array(
                'label'         => 'Carousel widgets',
                'route'         => 'admin',
                'controller'    => 'carousel',
                'action'        => 'index',
                'resource'      => array(
                    'resource'  => 'carousel',
                ),

                'pages'         => array(
                    'add'   => array(
                        'label'         => 'Add',
                        'route'         => 'admin',
                        'controller'    => 'carousel',
                        'action'        => 'add',
                        'visible'       => 0,
                    ),
                    'edit'   => array(
                        'label'         => 'Edit',
                        'route'         => 'admin',
                        'controller'    => 'carousel',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'tab'     => array(
                'label'         => 'Compound tabs',
                'route'         => 'admin',
                'controller'    => 'tab',
                'action'        => 'index',
                'resource'      => array(
                    'resource'  => 'tab',
                ),

                'pages'         => array(
                    'add'   => array(
                        'label'         => 'Add',
                        'route'         => 'admin',
                        'controller'    => 'tab',
                        'action'        => 'add',
                        'visible'       => 0,
                    ),
                    'edit'   => array(
                        'label'         => 'Edit',
                        'route'         => 'admin',
                        'controller'    => 'tab',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
        ),
    ),
);
