<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

/**
 * Page resource and config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'front'   => array(
        array(
            'title'      => _t('Article Homepage'),
            'controller' => 'article',
            'action'     => 'index',
            'block'      => 1,
        ),
        array(
            'title'      => _t('All Article List Page'),
            'controller' => 'list',
            'action'     => 'all',
            'block'      => 1,
        ),
        array(
            'title'      => _t('Category Related Article List Page'),
            'controller' => 'category',
            'action'     => 'list',
            'block'      => 1,
        ),
        array(
            'title'      => _t('Tag Related Article List Page'),
            'controller' => 'tag',
            'action'     => 'list',
            'block'      => 1,
        ),
        array(
            'title'      => _t('Article Detail Page'),
            'controller' => 'article',
            'action'     => 'detail',
            'block'      => 1,
        ),
        array(
            'title'      => _t('Search Result Page'),
            'controller' => 'search',
            'action'     => 'simple',
            'block'      => 1,
        ),
        array(
            'title'      => _t('Topic Homepage'),
            'controller' => 'topic',
            'action'     => 'index',
            'block'      => 1,
        ),
        array(
            'title'      => _t('Topic Article List Page'),
            'controller' => 'topic',
            'action'     => 'list',
            'block'      => 1,
        ),
    ),
    
    'admin'   => array(
        array(
            'controller'   => 'article',
            'permission'   => array(
                'parent'       => 'article',
            ),
        ),
        array(
            'controller'   => 'topic',
            'permission'   => array(
                'parent'       => 'topic',
            ),
        ),
        array(
            'controller'   => 'media',
            'permission'   => array(
                'parent'       => 'media',
            ),
        ),
        array(
            'controller'   => 'category',
            'permission'   => array(
                'parent'       => 'category',
            ),
        ),
        array(
            'controller'   => 'author',
            'permission'   => array(
                'parent'       => 'author',
            ),
        ),
        array(
            'controller'   => 'setup',
            'permission'   => array(
                'parent'       => 'setup',
            ),
        ),
        array(
            'controller'   => 'permission',
            'permission'   => array(
                'parent'       => 'permission',
            ),
        ),
        array(
            'controller'   => 'statistics',
            'permission'   => array(
                'parent'       => 'statistics',
            ),
        ),
    ),
);
