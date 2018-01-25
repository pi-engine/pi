<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    // Admin section
    'admin' => [
        [
            'controller' => 'script',
            'permission' => 'script',
        ],
        [
            'controller' => 'static',
            'permission' => 'static',
        ],
        [
            'controller' => 'list',
            'permission' => 'list',
        ],
        [
            'controller' => 'media',
            'permission' => 'media',
        ],
        [
            'controller' => 'carousel',
            'permission' => 'carousel',
        ],
        [
            'controller' => 'spotlight',
            'permission' => 'spotlight',
        ],
        [
            'controller' => 'tab',
            'permission' => 'tab',
        ],
        [
            'controller' => 'video',
            'permission' => 'video',
        ],
    ],
];
