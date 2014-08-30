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
                'controller'    => 'script',
                'action'        => 'index',
                'permission'    => array(
                    'resource'  => 'script',
                ),

                'pages'         => array(
                    'list'   => array(
                        'label'         => _t('Widget list'),
                        'route'         => 'admin',
                        'controller'    => 'script',
                        'action'        => 'index',
                    ),
                    'add'   => array(
                        'label'         => _t('Activate'),
                        'route'         => 'admin',
                        'controller'    => 'script',
                        'action'        => 'add',
                    ),
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
                        'label'         => _t('Widget list'),
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

            'list'         => array(
                'label'         => _t('List group'),
                'route'         => 'admin',
                'controller'    => 'list',
                'permission'    => array(
                    'resource'  => 'list',
                ),

                'pages'         => array(
                    'list'   => array(
                        'label'         => _t('Widget list'),
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

            'media'         => array(
                'label'         => _t('Media list'),
                'route'         => 'admin',
                'controller'    => 'media',
                'permission'    => array(
                    'resource'  => 'media',
                ),

                'pages'         => array(
                    'list'   => array(
                        'label'         => _t('Widget list'),
                        'route'         => 'admin',
                        'controller'    => 'media',
                        'action'        => 'index',

                        'pages'         => array(
                            'edit'   => array(
                                'label'         => _t('Edit'),
                                'route'         => 'admin',
                                'controller'    => 'media',
                                'action'        => 'edit',
                                'visible'       => 0,
                            ),
                        )
                    ),
                    'add'   => array(
                        'label'         => _t('Add new'),
                        'route'         => 'admin',
                        'controller'    => 'media',
                        'action'        => 'add',
                    ),
                ),
            ),

            'carousel'     => array(
                'label'         => _t('Carousel'),
                'route'         => 'admin',
                'controller'    => 'carousel',
                'permission'    => array(
                    'resource'  => 'carousel',
                ),

                'pages'         => array(
                    'list'   => array(
                        'label'         => _t('Widget list'),
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

            'spotlight'     => array(
                'label'         => _t('Spotlight'),
                'route'         => 'admin',
                'controller'    => 'spotlight',
                'permission'    => array(
                    'resource'  => 'spotlight',
                ),

                'pages'         => array(
                    'list'   => array(
                        'label'         => _t('Widget list'),
                        'route'         => 'admin',
                        'controller'    => 'spotlight',
                        'action'        => 'index',

                        'pages'         => array(
                            'edit'   => array(
                                'label'         => _t('Edit'),
                                'route'         => 'admin',
                                'controller'    => 'spotlight',
                                'action'        => 'edit',
                                'visible'       => 0,
                            ),
                        )
                    ),
                    'add'   => array(
                        'label'         => _t('Add new'),
                        'route'         => 'admin',
                        'controller'    => 'spotlight',
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
                        'label'         => _t('Widget list'),
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
                        'label'         => _t('Add new'),
                        'route'         => 'admin',
                        'controller'    => 'tab',
                        'action'        => 'add',
                    ),
                ),
            ),
        ),
    ),
);
