<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    'item' => [
        'front' => false,
        'admin' => [
            'script' => [
                'label'      => _t('Script widgets'),
                'route'      => 'admin',
                'controller' => 'script',
                'action'     => 'index',
                'permission' => [
                    'resource' => 'script',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('Widget list'),
                        'route'      => 'admin',
                        'controller' => 'script',
                        'action'     => 'index',
                    ],
                    'add'  => [
                        'label'      => _t('Activate'),
                        'route'      => 'admin',
                        'controller' => 'script',
                        'action'     => 'add',
                    ],
                ],
            ],

            'static' => [
                'label'      => _t('Static widgets'),
                'route'      => 'admin',
                'controller' => 'static',
                'permission' => [
                    'resource' => 'static',
                ],

                'pages' => [
                    'list'     => [
                        'label'      => _t('Widget list'),
                        'route'      => 'admin',
                        'controller' => 'static',
                        'action'     => 'index',

                        'pages' => [
                            'edit' => [
                                'label'      => _t('Edit'),
                                'route'      => 'admin',
                                'controller' => 'static',
                                'action'     => 'edit',
                                'visible'    => 0,
                            ],
                        ],
                    ],
                    'html'     => [
                        'label'      => _t('Add HTML'),
                        'route'      => 'admin',
                        'controller' => 'static',
                        'action'     => 'add',
                        'params'     => [
                            'type' => 'html',
                        ],
                    ],
                    'text'     => [
                        'label'      => _t('Add text'),
                        'route'      => 'admin',
                        'controller' => 'static',
                        'action'     => 'add',
                        'params'     => [
                            'type' => 'text',
                        ],
                    ],
                    'markdown' => [
                        'label'      => _t('Add markdown'),
                        'route'      => 'admin',
                        'controller' => 'static',
                        'action'     => 'add',
                        'params'     => [
                            'type' => 'markdown',
                        ],
                    ],
                ],
            ],

            'list' => [
                'label'      => _t('List group'),
                'route'      => 'admin',
                'controller' => 'list',
                'permission' => [
                    'resource' => 'list',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('Widget list'),
                        'route'      => 'admin',
                        'controller' => 'list',
                        'action'     => 'index',

                        'pages' => [
                            'edit' => [
                                'label'      => _t('Edit'),
                                'route'      => 'admin',
                                'controller' => 'list',
                                'action'     => 'edit',
                                'visible'    => 0,
                            ],
                        ],
                    ],
                    'add'  => [
                        'label'      => _t('Add new'),
                        'route'      => 'admin',
                        'controller' => 'list',
                        'action'     => 'add',
                    ],
                ],
            ],

            'media' => [
                'label'      => _t('Media list'),
                'route'      => 'admin',
                'controller' => 'media',
                'permission' => [
                    'resource' => 'media',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('Widget list'),
                        'route'      => 'admin',
                        'controller' => 'media',
                        'action'     => 'index',

                        'pages' => [
                            'edit' => [
                                'label'      => _t('Edit'),
                                'route'      => 'admin',
                                'controller' => 'media',
                                'action'     => 'edit',
                                'visible'    => 0,
                            ],
                        ],
                    ],
                    'add'  => [
                        'label'      => _t('Add new'),
                        'route'      => 'admin',
                        'controller' => 'media',
                        'action'     => 'add',
                    ],
                ],
            ],

            'carousel' => [
                'label'      => _t('Carousel'),
                'route'      => 'admin',
                'controller' => 'carousel',
                'permission' => [
                    'resource' => 'carousel',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('Widget list'),
                        'route'      => 'admin',
                        'controller' => 'carousel',
                        'action'     => 'index',

                        'pages' => [
                            'edit' => [
                                'label'      => _t('Edit'),
                                'route'      => 'admin',
                                'controller' => 'carousel',
                                'action'     => 'edit',
                                'visible'    => 0,
                            ],
                        ],
                    ],
                    'add'  => [
                        'label'      => _t('Add new'),
                        'route'      => 'admin',
                        'controller' => 'carousel',
                        'action'     => 'add',
                    ],
                ],
            ],

            'spotlight' => [
                'label'      => _t('Spotlight'),
                'route'      => 'admin',
                'controller' => 'spotlight',
                'permission' => [
                    'resource' => 'spotlight',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('Widget list'),
                        'route'      => 'admin',
                        'controller' => 'spotlight',
                        'action'     => 'index',

                        'pages' => [
                            'edit' => [
                                'label'      => _t('Edit'),
                                'route'      => 'admin',
                                'controller' => 'spotlight',
                                'action'     => 'edit',
                                'visible'    => 0,
                            ],
                        ],
                    ],
                    'add'  => [
                        'label'      => _t('Add new'),
                        'route'      => 'admin',
                        'controller' => 'spotlight',
                        'action'     => 'add',
                    ],
                ],
            ],

            'tab' => [
                'label'      => _t('Compound tabs'),
                'route'      => 'admin',
                'controller' => 'tab',
                'permission' => [
                    'resource' => 'tab',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('Widget list'),
                        'route'      => 'admin',
                        'controller' => 'tab',
                        'action'     => 'index',

                        'pages' => [
                            'edit' => [
                                'label'      => _t('Edit'),
                                'route'      => 'admin',
                                'controller' => 'tab',
                                'action'     => 'edit',
                                'visible'    => 0,
                            ],
                        ],
                    ],
                    'add'  => [
                        'label'      => _t('Add new'),
                        'route'      => 'admin',
                        'controller' => 'tab',
                        'action'     => 'add',
                    ],
                ],
            ],

            'video' => [
                'label'      => _t('Video and audio'),
                'route'      => 'admin',
                'controller' => 'video',
                'permission' => [
                    'resource' => 'video',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('Widget list'),
                        'route'      => 'admin',
                        'controller' => 'video',
                        'action'     => 'index',

                        'pages' => [
                            'edit' => [
                                'label'      => _t('Edit'),
                                'route'      => 'admin',
                                'controller' => 'video',
                                'action'     => 'edit',
                                'visible'    => 0,
                            ],
                        ],
                    ],
                    'add'  => [
                        'label'      => _t('Add new'),
                        'route'      => 'admin',
                        'controller' => 'video',
                        'action'     => 'add',
                    ],
                ],
            ],
        ],
    ],
];
