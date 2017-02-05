<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Block specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // User bar
    'user-bar'  => array(
        'title'         => _a('User bar'),
        'description'   => _a('User profile or login bar'),
        'render'        => 'block::userbar',
        'template'      => 'user-bar',
        'config'        => array(
            'type'      => array(
                'title'         => _a('Display type'),
                'description'   => _a('Mode to render.'),
                'value'         => 'flat',
                'edit'          => array(
                    'type'      => 'select',
                    'options'   => array(
                        'options'   => array(
                            'flat'         => _a('Flat'),
                            'mixed'        => _a('Flat on account / dropdown on other pages'),
                            'mixed_light'  => _a('Flat on account / dropdown on other pages -- No title for notification/message only'),
                            'dropdown'     => _a('Dropdown menu'),
                        ),
                    ),
                ),
            ),
            'float' => array(
                'title'         => _a('User bar float'),
                'description'   => '',
                'value'         => 'right',
                'edit'          => array(
                    'type'      => 'select',
                    'options'   => array(
                        'options'   => array(
                            'right' => _a('User bar on Right'),
                            'left'  => _a('User bar on left'),
                        ),
                    ),
                ),
            ),
            'show_title' => array(
                'title' => _a('Display title'),
                'description' => _a('Display menu title or just display icons'),
                'edit' => 'checkbox',
                'filter' => 'number_int',
                'value' => 1,
            ),
            'show_message'      => array(
                'title'         => _a('Display message'),
                'description'   => _a('Display message type'),
                'value'         => 'merge',
                'edit'          => array(
                    'type'      => 'select',
                    'options'   => array(
                        'options'   => array(
                            'none'          => _a('Don\'t display message and notification'),
                            'boot'          => _a('Message and notification separately'),
                            'message'       => _a('Just message'),
                            'notification'  => _a('Just notification'),
                            'merge'         => _a('Merge message and notification'),
                        ),
                    ),
                ),
            ),
            'show_order' => array(
                'title' => _a('Display order'),
                'description' => '',
                'edit' => 'checkbox',
                'filter' => 'number_int',
                'value' => 1,
            ),
            'show_credit' => array(
                'title' => _a('Display credit'),
                'description' => '',
                'edit' => 'checkbox',
                'filter' => 'number_int',
                'value' => 1,
            ),
            'show_support' => array(
                'title' => _a('Display support'),
                'description' => '',
                'edit' => 'checkbox',
                'filter' => 'number_int',
                'value' => 1,
            ),
            'show_favourite' => array(
                'title' => _a('Display favourite'),
                'description' => '',
                'edit' => 'checkbox',
                'filter' => 'number_int',
                'value' => 1,
            ),
        ),
    ),

    // Login block
    'login' => array(
        'title'         => _a('Login'),
        'description'   => _a('User login block'),
        'render'        => 'block::login',
        'template'      => 'login',
    ),

    // User information block
    'user'  => array(
        'title'         => _a('User'),
        'description'   => _a('User account'),
        'render'        => 'block::user',
        'template'      => 'user',
    ),

    // Site info block
    'site-info'   => array(
        'title'         => _a('Site Info'),
        'description'   => _a('Website information'),
        'render'        => 'block::site',
        'template'      => 'site-info',
    ),

    // Pi feature block
    'pi'    => array(
        'title'         => _a('Pi feature'),
        'description'   => _a('Introduction to Pi Engine'),
        'render'        => 'block::pi',
        'template'      => 'pi',
    ),

);