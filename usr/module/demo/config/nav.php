<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    //'translate' => 'navigation',
    'front' => [
        'pagea'  => [
            'label'      => _a('Homepage'),
            'route'      => 'default',
            'controller' => 'index',
            'action'     => 'index',

            'pages' => [
                'paginator' => [
                    'label'      => _a('Full Paginator'),
                    'route'      => 'default',
                    'controller' => 'index',
                    'action'     => 'page',
                ],
                'simple'    => [
                    'label'      => _a('Lean Paginator'),
                    'route'      => 'default',
                    'controller' => 'index',
                    'action'     => 'simple',
                ],
                'pageaa'    => [
                    'label'      => _a('Subpage one'),
                    'route'      => 'default',
                    'controller' => 'index',
                    'action'     => 'index',
                ],
                'pageab'    => [
                    'label'      => _a('Subpage two'),
                    'route'      => 'default',
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => [
                        'op' => 'test',
                    ],

                    'pages' => [
                        'pageaba' => [
                            'label'      => _a('Leaf one'),
                            'route'      => 'default',
                            'controller' => 'index',
                            'action'     => 'index',
                            'params'     => [
                                'op'   => 'test',
                                'page' => 2,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'pages'  => [
            'label'      => _a('Pages'),
            'route'      => 'default',
            'controller' => 'page',
        ],
        'files'  => [
            'label'      => _a('Files'),
            'route'      => 'default',
            'controller' => 'file',
        ],
        'images' => [
            'label'      => _a('Images'),
            'route'      => 'default',
            'controller' => 'image',
        ],
        'forms'  => [
            'label'      => _a('Forms'),
            'route'      => 'default',
            'controller' => 'form',
        ],
        'route'  => [
            'label'      => _a('Routes'),
            'route'      => 'default',
            'controller' => 'route',
        ],
        'tree'   => [
            'label'      => _a('Test User Call'),
            'route'      => 'default',
            'controller' => 'index',
            'action'     => 'user',
        ],
    ],
    'admin' => [
        'pagea' => [
            'label'      => _t('Sample'),
            'route'      => 'admin',
            'controller' => 'index',
            'action'     => 'index',
            'fragment'   => '!/test',
        ],
        'route' => [
            'label'      => _t('Routes'),
            'route'      => 'admin',
            'controller' => 'route',
            //'action'        => '!/action',
            'fragment'   => '!/action',
        ],
        [
            'label'      => _t('Form'),
            'route'      => 'admin',
            'controller' => 'form',
            'action'     => 'index',
        ],
    ],
];
