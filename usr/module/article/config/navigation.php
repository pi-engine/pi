<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Navigation config
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
return [
    'meta' => [
        'cms' => [
            'title'   => _a('Article site navigation'),
            'section' => 'front',
        ],
    ],
    'item' => [
        // Default front navigation
        'front' => [
            'article-homepage' => [
                'label'      => _a('Homepage'),
                'route'      => 'default',
                'controller' => 'index',
                'action'     => 'index',
            ],
            'my-draft'         => [
                'label'      => _a('My Draft'),
                'route'      => 'default',
                'controller' => 'article',
                'action'     => 'published',
                'params'     => [
                    'from' => 'my',
                ],
            ],
        ],

        // Default admin navigation
        'admin' => [
            'article'  => [
                'label'      => _t('All Article'),
                'route'      => 'admin',
                'controller' => 'article',
                'action'     => 'published',
                'params'     => [
                    'from' => 'all',
                ],
                'permission' => [
                    'resource' => 'article',
                ],

                'pages' => [
                    'published' => [
                        'label'      => _t('Published'),
                        'route'      => 'admin',
                        'controller' => 'article',
                        'action'     => 'published',
                        'params'     => [
                            'from' => 'all',
                        ],
                    ],
                    'pending'   => [
                        'label'      => _t('Pending'),
                        'route'      => 'admin',
                        'controller' => 'draft',
                        'action'     => 'list',
                        'params'     => [
                            'from'   => 'all',
                            'status' => 2,
                        ],
                    ],
                ],
            ],
            'compose'  => [
                'label'      => _t('Compose'),
                'route'      => 'admin',
                'controller' => 'draft',
                'action'     => 'add',
                'permission' => [
                    'resource' => 'compose',
                ],
            ],
            'my'       => [
                'label'      => _t('My Article'),
                'route'      => 'admin',
                'controller' => 'article',
                'action'     => 'published',
                'params'     => [
                    'from' => 'my',
                ],

                'pages' => [
                    'published' => [
                        'label'      => _t('Published'),
                        'route'      => 'admin',
                        'controller' => 'article',
                        'action'     => 'published',
                        'params'     => [
                            'from' => 'my',
                        ],
                    ],
                    'pending'   => [
                        'label'      => _t('Pending'),
                        'route'      => 'admin',
                        'controller' => 'draft',
                        'action'     => 'list',
                        'params'     => [
                            'from'   => 'my',
                            'status' => 2,
                        ],
                    ],
                    'rejected'  => [
                        'label'      => _t('Rejected'),
                        'route'      => 'admin',
                        'controller' => 'draft',
                        'action'     => 'list',
                        'params'     => [
                            'from'   => 'my',
                            'status' => 3,
                        ],
                    ],
                    'draft'     => [
                        'label'      => _t('Draft'),
                        'route'      => 'admin',
                        'controller' => 'draft',
                        'action'     => 'list',
                        'params'     => [
                            'from'   => 'my',
                            'status' => 1,
                        ],
                    ],
                ],
            ],
            'topic'    => [
                'label'      => _t('Topic'),
                'route'      => 'admin',
                'controller' => 'topic',
                'action'     => 'list-topic',
                'permission' => [
                    'resource' => 'topic',
                ],

                'pages' => [
                    'list-topic'   => [
                        'label'      => _t('All topics'),
                        'route'      => 'admin',
                        'controller' => 'topic',
                        'action'     => 'list-topic',
                    ],
                    'add'          => [
                        'label'      => _t('Add topic'),
                        'route'      => 'admin',
                        'controller' => 'topic',
                        'action'     => 'add',
                    ],
                    'list-article' => [
                        'label'      => _t('List topic articles'),
                        'route'      => 'admin',
                        'controller' => 'topic',
                        'action'     => 'list-article',
                        'visible'    => 0,
                    ],
                    'pull'         => [
                        'label'      => _t('Pull topic articles'),
                        'route'      => 'admin',
                        'controller' => 'topic',
                        'action'     => 'pull',
                        'visible'    => 0,
                    ],
                    'edit'         => [
                        'label'      => _t('Edit topic'),
                        'route'      => 'admin',
                        'controller' => 'topic',
                        'action'     => 'edit',
                        'visible'    => 0,
                    ],
                ],
            ],
            'media'    => [
                'label'      => _t('Media'),
                'route'      => 'admin',
                'controller' => 'media',
                'action'     => 'list',
                'permission' => [
                    'resource' => 'media',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('Media list'),
                        'route'      => 'admin',
                        'controller' => 'media',
                        'action'     => 'list',
                    ],
                    'add'  => [
                        'label'      => _t('Upload Media'),
                        'route'      => 'admin',
                        'controller' => 'media',
                        'action'     => 'add',
                    ],
                    'edit' => [
                        'label'      => _t('Edit Media'),
                        'route'      => 'admin',
                        'controller' => 'media',
                        'action'     => 'edit',
                        'visible'    => 0,
                    ],
                ],
            ],
            'category' => [
                'label'      => _t('Category'),
                'route'      => 'admin',
                'controller' => 'category',
                'action'     => 'list',
                'permission' => [
                    'resource' => 'category',
                ],

                'pages' => [
                    'list'  => [
                        'label'      => _t('Category list'),
                        'route'      => 'admin',
                        'controller' => 'category',
                        'action'     => 'list',
                    ],
                    'add'   => [
                        'label'      => _t('Add Category'),
                        'route'      => 'admin',
                        'controller' => 'category',
                        'action'     => 'add',
                    ],
                    'edit'  => [
                        'label'      => _t('Edit Category'),
                        'route'      => 'admin',
                        'controller' => 'category',
                        'action'     => 'edit',
                        'visible'    => 0,
                    ],
                    'merge' => [
                        'label'      => _t('Merge Category'),
                        'route'      => 'admin',
                        'controller' => 'category',
                        'action'     => 'merge',
                        'visible'    => 1,
                    ],
                    'move'  => [
                        'label'      => _t('Move Category'),
                        'route'      => 'admin',
                        'controller' => 'category',
                        'action'     => 'move',
                        'visible'    => 1,
                    ],
                ],
            ],
            'author'   => [
                'label'      => _t('Author'),
                'route'      => 'admin',
                'controller' => 'author',
                'action'     => 'list',
                'permission' => [
                    'resource' => 'author',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('Author list'),
                        'route'      => 'admin',
                        'controller' => 'author',
                        'action'     => 'list',
                    ],
                    'add'  => [
                        'label'      => _t('Add author'),
                        'route'      => 'admin',
                        'controller' => 'author',
                        'action'     => 'add',
                    ],
                    'edit' => [
                        'label'      => _t('Edit author'),
                        'route'      => 'admin',
                        'controller' => 'author',
                        'action'     => 'edit',
                        'visible'    => 0,
                    ],
                ],
            ],
            'setup'    => [
                'label'      => _t('Setup'),
                'route'      => 'admin',
                'controller' => 'setup',
                'action'     => 'form',
                'permission' => [
                    'resource' => 'setup',
                ],

                'pages' => [
                    'custom-form'  => [
                        'label'      => _t('Custom form'),
                        'route'      => 'admin',
                        'controller' => 'setup',
                        'action'     => 'form',
                    ],
                    'form-preview' => [
                        'label'      => _t('Preview form'),
                        'route'      => 'admin',
                        'controller' => 'setup',
                        'action'     => 'preview',
                        'visible'    => 0,
                    ],
                ],
            ],
            'analysis' => [
                'label'      => _t('Statistics'),
                'route'      => 'admin',
                'controller' => 'stats',
                'permission' => [
                    'resource' => 'stats',
                ],
            ],
        ],

        // Custom front navigation, need setup at backend
        'cms'   => [
            'article-homepage' => [
                'label'      => _a('Article Homepage'),
                'route'      => 'default',
                'controller' => 'article',
            ],
            'topic'            => [
                'label'      => _a('Topic'),
                'route'      => 'default',
                'controller' => 'topic',
                'action'     => 'all-topic',
            ],
            'draft'            => [
                'label'      => _a('My Article'),
                'route'      => 'default',
                'controller' => 'article',
                'action'     => 'article',
                'params'     => [
                    'from' => 'my',
                ],
            ],
        ],
    ],
];
