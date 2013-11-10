<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
            'admin'     => array(
                'label'     => _a('Admin'),
                'route'     => 'home',
                'section'   => 'admin',
            ),
            'feed'     => array(
                'label'     => _a('Feed'),
                'route'     => 'feed',
            ),
        ),
    ),

    // Account
    'account'   => array(
        'label'         => _a('Account'),
        'route'         => 'sysuser',
        'controller'    => 'account',
        //'visible'       => 0,
        'pages'         => array(
            'profile'     => array(
                'label'         => _a('Profile'),
                'route'         => 'sysuser',
                'controller'    => 'profile',
                'visible'       => 0,
            ),
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
            'email'     => array(
                'label'         => _a('Email'),
                'route'         => 'sysuser',
                'controller'    => 'email',
                'visible'       => 0,
            ),
        ),
    ),

    'modules'   => array(
        'callback'  => array('navigation', 'front'),
    ),
);
