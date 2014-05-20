<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    'item'  => array(
        'front'     => false,
        'admin'     => array(
            'script'     => array(
                'label'         => _t('Script widgets'),
                'route'         => 'admin',
                'controller'    => 'index',
                'action'        => 'index',
                'permission'    => array(
                    'resource'  => 'script',
                ),
            ),
            'static'     => array(
                'label'         => _t('Static widgets'),
                'route'         => 'admin',
                'controller'    => 'static',
                'permission'    => array(
                    'resource'  => 'static',
                ),

                'pages'         => array(
                    'list'   => array(
                        'label'         => _t('Static list'),
                        'route'         => 'admin',
                        'controller'    => 'static',
                        'action'        => 'index',

                        'pages'         => array(
                            'edit'   => array(
                                'label'         => _t('Edit'),
                                'route'         => 'admin',
                                'controller'    => 'static',
                                'action'        => 'edit',
                                'visible'       => 0,
                            ),
                        )
                    ),
                    'html'   => array(
                        'label'         => _t('Add HTML'),
                        'route'         => 'admin',
                        'controller'    => 'static',
                        'action'        => 'add',
                        'params'        => array(
                            'type'  => 'html'
                        )
                    ),
                    'text'   => array(
                        'label'         => _t('Add text'),
                        'route'         => 'admin',
                        'controller'    => 'static',
                        'action'        => 'add',
                        'params'        => array(
                            'type'  => 'text'
                        )
                    ),
                    'markdown'   => array(
                        'label'         => _t('Add markdown'),
                        'route'         => 'admin',
                        'controller'    => 'static',
                        'action'        => 'add',
                        'params'        => array(
                            'type'  => 'markdown'
                        )
                    ),
                ),
            ),
            'carousel'     => array(
                'label'         => _t('Carousel widgets'),
                'route'         => 'admin',
                'controller'    => 'carousel',
                'permission'    => array(
                    'resource'  => 'carousel',
                ),

                'pages'         => array(
                    'list'   => array(
                        'label'         => _t('Carousel list'),
                        'route'         => 'admin',
                        'controller'    => 'carousel',
                        'action'        => 'index',

                        'pages'         => array(
                            'edit'   => array(
                                'label'         => _t('Edit'),
                                'route'         => 'admin',
                                'controller'    => 'carousel',
                                'action'        => 'edit',
                                'visible'       => 0,
                            ),
                        )
                    ),
                    'add'   => array(
                        'label'         => _t('Add new'),
                        'route'         => 'admin',
                        'controller'    => 'carousel',
                        'action'        => 'add',
                    ),
                   
                ),
            ),
            'list'         => array(
                'label'         => _t('List widgets'),
                'route'         => 'admin',
                'controller'    => 'list',
                'permission'    => array(
                    'resource'  => 'list',
                ),

                'pages'         => array(
                    'list'   => array(
                        'label'         => _t('List block list'),
                        'route'         => 'admin',
                        'controller'    => 'list',
                        'action'        => 'index',

                        'pages'         => array(
                            'edit'   => array(
                                'label'         => _t('Edit'),
                                'route'         => 'admin',
                                'controller'    => 'list',
                                'action'        => 'edit',
                                'visible'       => 0,
                            ),
                        )
                    ),
                    'add'   => array(
                        'label'         => _t('Add new'),
                        'route'         => 'admin',
                        'controller'    => 'list',
                        'action'        => 'add',
                    ),
                ),
            ),
            'tab'     => array(
                'label'         => _t('Compound tabs'),
                'route'         => 'admin',
                'controller'    => 'tab',
                'permission'    => array(
                    'resource'  => 'tab',
                ),

                'pages'         => array(
                    'list'   => array(
                        'label'         => _t('Tab list'),
                        'route'         => 'admin',
                        'controller'    => 'tab',
                        'action'        => 'index',

                        'pages'         => array(
                            'edit'   => array(
                                'label'         => _t('Edit'),
                                'route'         => 'admin',
                                'controller'    => 'tab',
                                'action'        => 'edit',
                                'visible'       => 0,
                            ),
                        )
                    ),
                    'add'   => array(
                        'label'         => _t('Add'),
                        'route'         => 'admin',
                        'controller'    => 'tab',
                        'action'        => 'add',
                    ),
                ),
            ),
        ),
    ),
);
