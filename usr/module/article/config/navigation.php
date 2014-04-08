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
    'meta'  => array(
        'cms'    => array(
            'title'     => _a('Article site navigation'),
            'section'   => 'front',
        ),
    ),
    'item'  => array(
        // Default front navigation
        'front'   => array(
            'article-homepage'  => array(
                'label'         => _a('Homepage'),
                'route'         => 'default',
                'controller'    => 'index',
                'action'        => 'index',
            ),
            'my-draft'          => array(
                'label'         => _a('My Draft'),
                'route'         => 'default',
                'controller'    => 'article',
                'action'        => 'published',
                'params'        => array(
                    'from'          => 'my',
                ),
            ),
        ),
        
        // Default admin navigation
        'admin'   => array(
            'article'           => array(
                'label'         => _t('All Article'),
                'route'         => 'admin',
                'controller'    => 'article',
                'action'        => 'published',
                'params'        => array(
                    'from'          => 'all',
                ),
                'permission'    => array(
                    'resource'  => 'article',
                ),

                'pages'         => array(
                    'published' => array(
                        'label'         => _t('Published'),
                        'route'         => 'admin',
                        'controller'    => 'article',
                        'action'        => 'published',
                        'params'        => array(
                            'from'          => 'all',
                        ),
                    ),
                    'pending'   => array(
                        'label'         => _t('Pending'),
                        'route'         => 'admin',
                        'controller'    => 'draft',
                        'action'        => 'list',
                        'params'        => array(
                            'from'          => 'all',
                            'status'        => 2,
                        ),
                    ),
                ),
            ),
            'compose'   => array(
                'label'         => _t('Compose'),
                'route'         => 'admin',
                'controller'    => 'draft',
                'action'        => 'add',
                'permission'    => array(
                    'resource'  => 'compose',
                ),
            ),
            'my'                => array(
                'label'         => _t('My Article'),
                'route'         => 'admin',
                'controller'    => 'article',
                'action'        => 'published',
                'params'        => array(
                    'from'          => 'my',
                ),

                'pages'         => array(
                    'published' => array(
                        'label'         => _t('Published'),
                        'route'         => 'admin',
                        'controller'    => 'article',
                        'action'        => 'published',
                        'params'        => array(
                            'from'          => 'my',
                        ),
                    ),
                    'pending'   => array(
                        'label'         => _t('Pending'),
                        'route'         => 'admin',
                        'controller'    => 'draft',
                        'action'        => 'list',
                        'params'        => array(
                            'from'          => 'my',
                            'status'        => 2,
                        ),
                    ),
                    'rejected'   => array(
                        'label'         => _t('Rejected'),
                        'route'         => 'admin',
                        'controller'    => 'draft',
                        'action'        => 'list',
                        'params'        => array(
                            'from'          => 'my',
                            'status'        => 3,
                        ),
                    ),
                    'draft'   => array(
                        'label'         => _t('Draft'),
                        'route'         => 'admin',
                        'controller'    => 'draft',
                        'action'        => 'list',
                        'params'        => array(
                            'from'          => 'my',
                            'status'        => 1,
                        ),
                    ),
                ),
            ),
            'topic'             => array(
                'label'         => _t('Topic'),
                'route'         => 'admin',
                'controller'    => 'topic',
                'action'        => 'list-topic',
                'permission'    => array(
                    'resource'  => 'topic',
                ),
                
                'pages'         => array(
                    'list-topic'    => array(
                        'label'         => _t('All topics'),
                        'route'         => 'admin',
                        'controller'    => 'topic',
                        'action'        => 'list-topic',
                    ),
                    'add'           => array(
                        'label'         => _t('Add topic'),
                        'route'         => 'admin',
                        'controller'    => 'topic',
                        'action'        => 'add',
                    ),
                    'list-article'  => array(
                        'label'         => _t('List topic articles'),
                        'route'         => 'admin',
                        'controller'    => 'topic',
                        'action'        => 'list-article',
                        'visible'       => 0,
                    ),
                    'pull'          => array(
                        'label'         => _t('Pull topic articles'),
                        'route'         => 'admin',
                        'controller'    => 'topic',
                        'action'        => 'pull',
                        'visible'       => 0,
                    ),
                    'edit'          => array(
                        'label'         => _t('Edit topic'),
                        'route'         => 'admin',
                        'controller'    => 'topic',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'media'             => array(
                'label'         => _t('Media'),
                'route'         => 'admin',
                'controller'    => 'media',
                'action'        => 'list',
                'permission'    => array(
                    'resource'  => 'media',
                ),
                
                'pages'         => array(
                    'list'          => array(
                        'label'         => _t('Media list'),
                        'route'         => 'admin',
                        'controller'    => 'media',
                        'action'        => 'list',
                    ),
                    'add'           => array(
                        'label'         => _t('Upload Media'),
                        'route'         => 'admin',
                        'controller'    => 'media',
                        'action'        => 'add',
                    ),
                    'edit'          => array(
                        'label'         => _t('Edit Media'),
                        'route'         => 'admin',
                        'controller'    => 'media',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'category'          => array(
                'label'         => _t('Category'),
                'route'         => 'admin',
                'controller'    => 'category',
                'action'        => 'list',
                'permission'    => array(
                    'resource'  => 'category',
                ),
                
                'pages'         => array(
                    'list'          => array(
                        'label'         => _t('Category list'),
                        'route'         => 'admin',
                        'controller'    => 'category',
                        'action'        => 'list',
                    ),
                    'add'           => array(
                        'label'         => _t('Add Category'),
                        'route'         => 'admin',
                        'controller'    => 'category',
                        'action'        => 'add',
                    ),
                    'edit'          => array(
                        'label'         => _t('Edit Category'),
                        'route'         => 'admin',
                        'controller'    => 'category',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                    'merge'         => array(
                        'label'         => _t('Merge Category'),
                        'route'         => 'admin',
                        'controller'    => 'category',
                        'action'        => 'merge',
                        'visible'       => 1,
                    ),
                    'move'          => array(
                        'label'         => _t('Move Category'),
                        'route'         => 'admin',
                        'controller'    => 'category',
                        'action'        => 'move',
                        'visible'       => 1,
                    ),
                ),
            ),
            'author'            => array(
                'label'         => _t('Author'),
                'route'         => 'admin',
                'controller'    => 'author',
                'action'        => 'list',
                'permission'    => array(
                    'resource'  => 'author',
                ),
                
                'pages'         => array(
                    'list'          => array(
                        'label'         => _t('Author list'),
                        'route'         => 'admin',
                        'controller'    => 'author',
                        'action'        => 'list',
                    ),
                    'add'           => array(
                        'label'         => _t('Add author'),
                        'route'         => 'admin',
                        'controller'    => 'author',
                        'action'        => 'add',
                    ),
                    'edit'          => array(
                        'label'         => _t('Edit author'),
                        'route'         => 'admin',
                        'controller'    => 'author',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'setup'             => array(
                'label'         => _t('Setup'),
                'route'         => 'admin',
                'controller'    => 'setup',
                'action'        => 'form',
                'permission'    => array(
                    'resource'  => 'setup',
                ),
                
                'pages'         => array(
                    'custom-form'   => array(
                        'label'         => _t('Custom form'),
                        'route'         => 'admin',
                        'controller'    => 'setup',
                        'action'        => 'form',
                    ),
                    'form-preview'  => array(
                        'label'         => _t('Preview form'),
                        'route'         => 'admin',
                        'controller'    => 'setup',
                        'action'        => 'preview',
                        'visible'       => 0,
                    ),
                ),
            ),
            'analysis'          => array(
                'label'         => _t('Statistics'),
                'route'         => 'admin',
                'controller'    => 'stats',
                'permission'    => array(
                    'resource'  => 'stats',
                ),
            ),
        ),
        
        // Custom front navigation, need setup at backend
        'cms'     => array(
            'article-homepage'  => array(
                'label'         => _a('Article Homepage'),
                'route'         => 'default',
                'controller'    => 'article',
            ),
            'topic'             => array(
                'label'         => _a('Topic'),
                'route'         => 'default',
                'controller'    => 'topic',
                'action'        => 'all-topic',
            ),
            'draft'             => array(
                'label'         => _a('My Article'),
                'route'         => 'default',
                'controller'    => 'article',
                'action'        => 'article',
                'params'        => array(
                    'from'         => 'my',
                ),
            ),
        ),
    ),
);
