<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Custom route config
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
return [
    'article' => [
        'section'  => 'front',
        'priority' => 100,

        'type'    => 'Module\Article\Route\Article',
        'options' => [
            'structure_delimiter' => '/',
            'param_delimiter'     => '/',
            'key_value_delimiter' => '-',
            //'prefix'                => '/article',
            'defaults'            => [
                'module'     => 'article',
                'controller' => 'index',
                'action'     => 'index',
            ],
        ],
    ],
];
