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
        'label'         => _t('Home'),
        'route'         => 'home',

        'pages'         => array(
            'front'     => array(
                'label'     => _t('Front'),
                'route'     => 'home',
            ),
            'admin'     => array(
                'label'     => _t('Admin'),
                'route'     => 'home',
                'section'   => 'admin',
            ),
        ),
    ),

    // Account
    'account'   => array(
        'label'         => _t('Account'),
        'route'         => 'sysuser',
        'controller'    => 'account',
        //'visible'       => 0,
        'pages'         => array(
            'profile'     => array(
                'label'         => _t('Profile'),
                'route'         => 'sysuser',
                'controller'    => 'profile',
                'visible'       => 0,
            ),
            'login'     => array(
                'label'         => _t('Login'),
                'route'         => 'sysuser',
                'controller'    => 'login',
                'visible'       => 0,
            ),
            
            'register'     => array(
                'label'         => _t('Register'),
                'route'         => 'sysuser',
                'controller'    => 'register',
                'visible'       => 0,
            ),
            'password'     => array(
                'label'         => _t('Password'),
                'route'         => 'sysuser',
                'controller'    => 'password',
                'visible'       => 0,
            ),
            'email'     => array(
                'label'         => _t('Email'),
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
