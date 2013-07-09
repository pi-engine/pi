<?php
/**
 * System navigation config
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @version         $Id$
 */

return array(
    // Homepage
    'nav-home' => array(
        'order'         => -100,
        'label'         => 'Home',
        'route'         => 'home',
    ),

    // Account
    'account'   => array(
        'label'         => 'Account',
        'route'         => 'user',
        'controller'    => 'account',
        //'visible'       => 0,
        'pages'         => array(
            'profile'     => array(
                'label'         => 'Profile',
                'route'         => 'user',
                'controller'    => 'profile',
                'visible'       => 0,
            ),
            'login'     => array(
                'label'         => 'Login',
                'route'         => 'user',
                'controller'    => 'login',
                'visible'       => 0,
            ),
            'register'     => array(
                'label'         => 'Register',
                'route'         => 'user',
                'controller'    => 'register',
                'visible'       => 0,
            ),
            'password'     => array(
                'label'         => 'Password',
                'route'         => 'user',
                'controller'    => 'password',
                'visible'       => 0,
            ),
            'email'     => array(
                'label'         => 'Email',
                'route'         => 'user',
                'controller'    => 'email',
                'visible'       => 0,
            ),
        ),
    ),

    // Admin page
    'admin'     => array(
        'label'         => 'Admin Area',
        'route'         => 'admin',
        'resource'      => array(
            'module'    => 'system',
            'resource'  => 'member',
        ),
        //'visible'       => 0,
    ),

    'modules'   => array(
        'callback'  => array('navigation', 'front'),
    ),
);
