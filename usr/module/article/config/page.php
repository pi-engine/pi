<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Page resource and config
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
return [
    'front' => [
        [
            'title'      => _a('Article Homepage'),
            'controller' => 'article',
            'action'     => 'index',
            'block'      => 1,
        ],
        [
            'title'      => _a('All Article List Page'),
            'controller' => 'list',
            'action'     => 'all',
            'block'      => 1,
        ],
        [
            'title'      => _a('Category Related Article List Page'),
            'controller' => 'category',
            'action'     => 'list',
            'block'      => 1,
        ],
        [
            'title'      => _a('Tag Related Article List Page'),
            'controller' => 'tag',
            'action'     => 'list',
            'block'      => 1,
        ],
        [
            'title'      => _a('Article Detail Page'),
            'controller' => 'article',
            'action'     => 'detail',
            'block'      => 1,
        ],
        [
            'title'      => _a('Search Result Page'),
            'controller' => 'search',
            'action'     => 'simple',
            'block'      => 1,
        ],
        [
            'title'      => _a('Topic Homepage'),
            'controller' => 'topic',
            'action'     => 'index',
            'block'      => 1,
        ],
        [
            'title'      => _a('Topic Article List Page'),
            'controller' => 'topic',
            'action'     => 'list',
            'block'      => 1,
        ],
    ],

    'admin' => [
        [
            'controller' => 'article',
            'permission' => 'article',
        ],
        [
            'controller' => 'topic',
            'permission' => 'topic',
        ],
        [
            'controller' => 'media',
            'permission' => 'media',
        ],
        [
            'controller' => 'category',
            'permission' => 'category',
        ],
        [
            'controller' => 'author',
            'permission' => 'author',
        ],
        [
            'controller' => 'setup',
            'permission' => 'setup',
        ],
        [
            'controller' => 'permission',
            'permission' => 'permission',
        ],
        [
            'controller' => 'stats',
            'permission' => 'stats',
        ],
    ],
];
