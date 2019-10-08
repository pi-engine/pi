<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Route specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    // System user route
    'user' => [
        'name'     => 'user',
        'type'     => 'Module\User\Route\User',
        'priority' => 5,
        'options'  => [
            'route' => '/user',
        ],
    ],
];
