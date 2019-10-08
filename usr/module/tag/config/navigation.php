<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    'front' => false,
    'admin' => [
        'top'   => [
            'label'      => _t('Top tags'),
            'route'      => 'admin',
            'controller' => 'index',
            'action'     => 'top',
        ],
        'new'   => [
            'label'      => _t('New tags'),
            'route'      => 'admin',
            'controller' => 'index',
            'action'     => 'new',
        ],
        'items' => [
            'label'      => _t('Tagged items'),
            'route'      => 'admin',
            'controller' => 'index',
            'action'     => 'link',
        ],
    ],
];
