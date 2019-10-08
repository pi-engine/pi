<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    // route name
    'page' => [
        'name'     => 'page',
        'section'  => 'front',
        'priority' => 10,

        'type'    => 'Module\Page\Route\Page',
        'options' => [
            //'route'     => '/page',
            'defaults' => [
                'module'     => 'page',
                'controller' => 'index',
                'action'     => 'index',
            ],
        ],
    ],
];
