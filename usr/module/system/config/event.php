<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Event/listener specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    // Event list
    'event'    => [
        // event name (unique)
        'module_install'    => [
            // title
            'title' => _t('Module installed'),
        ],
        'module_uninstall'  => [
            'title' => _t('Module uninstalled'),
        ],
        'module_activate'   => [
            'title' => _t('Module activated'),
        ],
        'module_deactivate' => [
            'title' => _t('Module deactivated'),
        ],
        'module_update'     => [
            'title' => _t('Module updated'),
        ],
    ],
    // Listener list
    'listener' => [
        [
            // event info: module, event name
            'event'    => ['system', 'module_install'],
            // listener callback: class, method
            'callback' => ['event', 'moduleinstall'],
        ],
        [
            'event'    => ['system', 'module_uninstall'],
            'callback' => ['event', 'moduleuninstall'],
        ],
        [
            'event'    => ['system', 'module_update'],
            'callback' => ['event', 'moduleupdate'],
        ],
        [
            'event'    => ['system', 'module_activate'],
            'callback' => ['event', 'moduleactivate'],
        ],
        [
            'event'    => ['system', 'module_deactivate'],
            'callback' => ['event', 'moduledeactivate'],
        ],
    ],
];
