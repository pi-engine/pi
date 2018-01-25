<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    'meta' => [
        'site' => [
            'title'   => _t('Custom site navigation'),
            'section' => 'front',
        ],
    ],
    'item' => [
        'front' => false,
        'admin' => [
            'list'     => [
                'label'      => _t('Page list'),
                'route'      => 'admin',
                'controller' => 'index',
                'action'     => 'index',

                'pages' => [
                    'edit'   => [
                        'label'      => _t('Edit page'),
                        'route'      => 'admin',
                        'controller' => 'index',
                        'action'     => 'edit',
                        'visible'    => 0,
                    ],
                    'delete' => [
                        'label'      => _t('Delete page'),
                        'route'      => 'admin',
                        'controller' => 'index',
                        'action'     => 'delete',
                        'visible'    => 0,
                    ],
                ],
            ],
            'add'      => [
                'label'      => _t('Add a page'),
                'route'      => 'admin',
                'controller' => 'index',
                'action'     => 'add',
            ],
            'template' => [
                'label'      => _t('Template list'),
                'route'      => 'admin',
                'controller' => 'template',
                'action'     => 'index',
            ],
        ],

        // Custom navigation
        'site'  => [
            'home'    => [
                'label' => _a('Home'),
                'route' => 'home',
            ],
            'about'   => [
                'label'  => _a('About us'),
                'route'  => '.page',
                'action' => 'about',
            ],
            'contact' => [
                'label'  => _a('Contact us'),
                'route'  => '.page',
                'action' => 'contact',
            ],
            'term'    => [
                'label'  => _a('Terms of use'),
                'route'  => '.page',
                'action' => 'terms',
            ],
            'privacy' => [
                'label'  => _a('Privacy guidelines'),
                'route'  => '.page',
                'action' => 'privacy',
            ],
            'join'    => [
                'label' => _a('Join us'),
                'route' => 'user',
            ],
            'app'     => [
                'label'    => _a('Applications'),
                'uri'      => '',
                'callback' => 'Module\\Page\\Navigation::modules',
            ],
            'eefocus' => [
                'label'  => _a('Pi Engine'),
                'uri'    => 'http://piengine.org',
                'target' => '_blank',
            ],
        ],
    ],
];
