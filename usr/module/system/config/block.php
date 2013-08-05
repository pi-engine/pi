<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Block specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
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

    // Pi feature block
    'pi'    => array(
        'title'         => __('Pi feature'),
        'description'   => __('Introduction to Pi Engine'),
        'render'        => 'block::pi',
        'template'      => 'pi',
    ),

);
