<?php
/**
 * System module block config
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

// Block list
return array(
    // Site info block
    'site-info'   => array(
        'title'         => __('Site Info'),
        'description'   => __('Website information'),
        'render'        => 'block::site',
        'template'      => 'site-info',
    ),

    // User information block
    'user'  => array(
        'title'         => __('User'),
        'description'   => __('User account'),
        'render'        => 'block::user',
        'template'      => 'user',
    ),

    // User bar
    'user-bar'  => array(
        'title'         => __('User bar'),
        'description'   => __('User profile or login bar'),
        'render'        => 'block::userbar',
        'template'      => 'user-bar',
    ),

    // Login block
    'login' => array(
        'title'         => __('Login'),
        'description'   => __('User login block'),
        'render'        => 'block::login',
        'template'      => 'login',
    ),
);
