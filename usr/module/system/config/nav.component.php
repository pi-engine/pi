<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * System component navigation specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    'config' => [
        'label'      => _t('Configurations'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'config',

        'permission' => [
            'resource' => 'config',
        ],
    ],

    'block' => [
        'label'      => _t('Blocks'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'block',

        'permission' => [
            'resource' => 'block',
        ],
    ],

    'page' => [
        'label'      => _t('Pages'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'page',

        'permission' => [
            'resource' => 'page',
        ],
    ],

    'cache' => [
        'label'      => _t('Cache'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'cache',

        'permission' => [
            'resource' => 'cache',
        ],
    ],

    'perm' => [
        'label'      => _t('Permission'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'perm',

        'permission' => [
            'resource' => 'permission',
        ],
    ],

    'event' => [
        'label'      => _t('Event/Hook'),
        'route'      => 'admin',
        'module'     => 'system',
        'controller' => 'event',

        'permission' => [
            'resource' => 'event',
        ],
    ],
];
