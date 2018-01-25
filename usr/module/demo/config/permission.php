<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    'front' => [
        // test
        'test'   => [
            'title'  => _a('Test resource'),
            'access' => [
                'guest',
                'member',
            ],
        ],
        'write'  => [
            'title'  => _a('Write privilege'),
            'access' => 'member',
        ],
        'manage' => [
            'title'  => _a('Management privilege'),
            'access' => 'moderator',
        ],
        'custom' => 'Module\Demo\Api\PermFront',
    ],
    'admin' => [
        'custom' => 'Module\Demo\Api\PermAdmin',
    ],
];
