<?php
/**
 * System module route config
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

/**
 * Routes
 * @see lib\Pi\Mvc\Router\Route
 */
return array(
    // default route
    'default'   => array(
        'section'   => 'front',
        'priority'  => -999,

        'type'      => 'Standard',
        'options'   =>array(
            'structure_delimiter'   => '/',
            'param_delimiter'       => '/',
            'key_value_delimiter'   => '-',
            'defaults'              => array(
                'module'        => 'system',
                'controller'    => 'index',
                'action'        => 'index',
            )
        )
    ),

    // Home route
    'home'  => array(
        'type'      => 'Home',
        'priority'  => 10000,

        'options'   =>array(
            'structure_delimiter'   => '-',
            'param_delimiter'       => '/',
            'key_value_delimiter'   => '-',
        ),
    ),

    // admin route
    'admin' => array(
        // section, default as 'front'
        'section'   => 'admin',
        'priority'  => 100,

        'type'      => 'Standard',
        'options'   => array(
            'route'     => '/admin',
        ),
    ),

    // feed route
    'feed' => array(
        'section'   => 'feed',
        'priority'  => 100,

        'type'      => 'Feed',
        'options'   => array(
            'route'     => '/feed',
        ),
    ),

    // User route
    'user'  => array(
        'type'      => 'User',
        'priority'  => 5,
    ),

    // Transition page jump route
    'jump' => array(
        'priority'  => 5,

        'type'      => 'Literal',
        'options'   => array(
            'route'     => '/jump',
            'defaults'  => array(
                'module'        => 'system',
                'controller'    => 'index',
                'action'        => 'jump'
            )
        ),
    ),
);
