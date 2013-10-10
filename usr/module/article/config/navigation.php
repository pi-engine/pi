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
    'meta'  => array(
        'cms'    => array(
            'title'     => _t('Article site navigation'),
            'section'   => 'front',
        ),
    ),
    'item'  => array(
        // Default front navigation
        'front'   => array(
            'article-homepage'  => array(
                'label'         => _t('Homepage'),
                'route'         => 'default',
                'controller'    => 'index',
                'action'        => 'index',
            ),
            'my-draft'          => array(
                'label'         => _t('My Draft'),
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
                'permission'     => array(
                    'resource'  => 'article',
                ),
            ),
            'topic'             => array(
                'label'         => _t('Topic'),
                'route'         => 'admin',
                'controller'    => 'topic',
                'action'        => 'list-topic',
                'permission'     => array(
                    'resource'  => 'topic',
                ),
            ),
            'media'             => array(
                'label'         => _t('Media'),
                'route'         => 'admin',
                'controller'    => 'media',
                'action'        => 'list',
                'permission'     => array(
                    'resource'  => 'media',
                ),
            ),
            'category'          => array(
                'label'         => _t('Category'),
                'route'         => 'admin',
                'controller'    => 'category',
                'action'        => 'list',
                'permission'     => array(
                    'resource'  => 'category',
                ),
            ),
            'author'            => array(
                'label'         => _t('Author'),
                'route'         => 'admin',
                'controller'    => 'author',
                'action'        => 'list',
                'permission'     => array(
                    'resource'  => 'author',
                ),
            ),
            'setup'             => array(
                'label'         => _t('Setup'),
                'route'         => 'admin',
                'controller'    => 'setup',
                'action'        => 'form',
                'permission'     => array(
                    'resource'  => 'setup',
                ),
            ),
            'permission'        => array(
                'label'         => _t('Permission'),
                'route'         => 'admin',
                'controller'    => 'permission',
                'permission'     => array(
                    'resource'  => 'permission',
                ),
            ),
            'analysis'          => array(
                'label'         => _t('Statistics'),
                'route'         => 'admin',
                'controller'    => 'statistics',
                'permission'     => array(
                    'resource'  => 'statistics',
                ),
            ),
        ),
        
        // Custom front navigation, need setup at backend
        'cms'     => array(
            'article-homepage'  => array(
                'label'         => _t('Article Homepage'),
                'route'         => 'default',
                'controller'    => 'article',
            ),
            'topic'             => array(
                'label'         => _t('Topic'),
                'route'         => 'default',
                'controller'    => 'topic',
                'action'        => 'all-topic',
            ),
            'draft'             => array(
                'label'         => _t('My Article'),
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
