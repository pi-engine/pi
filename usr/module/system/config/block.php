<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Block specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    // User bar
    'user-bar'  => [
        'title'       => _a('User bar'),
        'description' => _a('User profile or login bar'),
        'render'      => 'block::userbar',
        'template'    => 'user-bar',
        'config'      => [
            'type'           => [
                'title'       => _a('Display type'),
                'description' => _a('Mode to render.'),
                'value'       => 'flat',
                'edit'        => [
                    'type'    => 'select',
                    'options' => [
                        'options' => [
                            'flat'        => _a('Flat'),
                            'mixed'       => _a('Flat on account / dropdown on other pages'),
                            'mixed_light' => _a('Flat on account / dropdown on other pages -- No title for notification/message only'),
                            'dropdown'    => _a('Dropdown menu'),
                        ],
                    ],
                ],
            ],
            'float'          => [
                'title'       => _a('User bar float'),
                'description' => '',
                'value'       => 'right',
                'edit'        => [
                    'type'    => 'select',
                    'options' => [
                        'options' => [
                            'right' => _a('User bar on Right'),
                            'left'  => _a('User bar on left'),
                        ],
                    ],
                ],
            ],
            'show_title'     => [
                'title'       => _a('Display title'),
                'description' => _a('Display menu title or just display icons'),
                'edit'        => 'checkbox',
                'filter'      => 'number_int',
                'value'       => 1,
            ],
            'show_message'   => [
                'title'       => _a('Display message'),
                'description' => _a('Display message type'),
                'value'       => 'merge',
                'edit'        => [
                    'type'    => 'select',
                    'options' => [
                        'options' => [
                            'none'         => _a('Don\'t display message and notification'),
                            'boot'         => _a('Message and notification separately'),
                            'message'      => _a('Just message'),
                            'notification' => _a('Just notification'),
                            'merge'        => _a('Merge message and notification'),
                        ],
                    ],
                ],
            ],
            'show_order'     => [
                'title'       => _a('Display order'),
                'description' => '',
                'edit'        => 'checkbox',
                'filter'      => 'number_int',
                'value'       => 1,
            ],
            'show_credit'    => [
                'title'       => _a('Display credit'),
                'description' => '',
                'edit'        => 'checkbox',
                'filter'      => 'number_int',
                'value'       => 1,
            ],
            'show_support'   => [
                'title'       => _a('Display support'),
                'description' => '',
                'edit'        => 'checkbox',
                'filter'      => 'number_int',
                'value'       => 1,
            ],
            'show_favourite' => [
                'title'       => _a('Display favourite'),
                'description' => '',
                'edit'        => 'checkbox',
                'filter'      => 'number_int',
                'value'       => 1,
            ],
            'show_offer'     => [
                'title'       => _a('Display offer'),
                'description' => '',
                'edit'        => 'checkbox',
                'filter'      => 'number_int',
                'value'       => 1,
            ],
            'show_video'   => [
                'title'       => _a('Display video'),
                'description' => '',
                'edit'        => 'checkbox',
                'filter'      => 'number_int',
                'value'       => 1,
            ],
        ],
    ],

    // Login block
    'login'     => [
        'title'       => _a('Login'),
        'description' => _a('User login block'),
        'render'      => 'block::login',
        'template'    => 'login',
    ],

    // User information block
    'user'      => [
        'title'       => _a('User'),
        'description' => _a('User account'),
        'render'      => 'block::user',
        'template'    => 'user',
    ],

    // Site info block
    'site-info' => [
        'title'       => _a('Site Info'),
        'description' => _a('Website information'),
        'render'      => 'block::site',
        'template'    => 'site-info',
    ],

    // Pi feature block
    'pi'        => [
        'title'       => _a('Pi feature'),
        'description' => _a('Introduction to Pi Engine'),
        'render'      => 'block::pi',
        'template'    => 'pi',
    ],

];
