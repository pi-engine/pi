<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * System front navigation specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Homepage
    'nav-home' => array(
        'order'         => -100,
        'label'         => _a('Home'),
        'route'         => 'home',

        'pages'         => array(
            'account'   => array(
                'label'         => _a('Profile'),
                'route'         => 'sysuser',
                'controller'    => 'profile',

                'pages'         => array(
                    'login'     => array(
                        'label'         => _a('Login'),
                        'route'         => 'sysuser',
                        'controller'    => 'login',
                        'visible'       => 0,
                    ),

                    'register'     => array(
                        'label'         => _a('Register'),
                        'route'         => 'sysuser',
                        'controller'    => 'register',
                        'visible'       => 0,
                    ),

                    'password'     => array(
                        'label'         => _a('Password'),
                        'route'         => 'sysuser',
                        'controller'    => 'password',
                        'visible'       => 0,
                    ),
                ),
            ),
            'admin'     => array(
                'label'     => _a('Admin'),
                'route'     => 'home',
                'section'   => 'admin',
                'target'    => '_blank',
            ),
            'feed'     => array(
                'label'     => _a('RSS Feed'),
                'route'     => 'feed',
                'section'   => 'feed',
                'target'    => '_blank',
            ),
        ),
    ),

    'modules'   => array(
        'callback'  => array('navigation', 'front'),
    ),
);
