<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    // Event list
    'events'    => [
        // event name (unique)
        'user_call' => [
            // title
            'title' => _a('Event hook demo'),
        ],
    ],
    // Listener list
    'listeners' => [
        [
            // event info: module, event name
            'event'    => ['pm', 'test'],
            // listener callback: class, method
            'listener' => ['event', 'message'],
        ],
        [
            'event'    => ['demo', 'user_call'],
            'listener' => ['event', 'selfcall'],
        ],
        [
            'event'    => ['system', 'module_install'],
            'listener' => ['event', 'moduleinstall'],
        ],
        [
            'event'    => ['system', 'module_update'],
            'listener' => ['event', 'moduleupdate'],
        ],
    ],
];
