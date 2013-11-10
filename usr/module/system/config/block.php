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
        'title'         => _a('Site Info'),
        'description'   => _a('Website information'),
        'render'        => 'block::site',
        'template'      => 'site-info',
    ),

    // User information block
    'user'  => array(
        'title'         => _a('User'),
        'description'   => _a('User account'),
        'render'        => 'block::user',
        'template'      => 'user',
    ),

    // User bar
    'user-bar'  => array(
        'title'         => _a('User bar'),
        'description'   => _a('User profile or login bar'),
        'render'        => 'block::userbar',
        'template'      => 'user-bar',
    ),

    // Login block
    'login' => array(
        'title'         => _a('Login'),
        'description'   => _a('User login block'),
        'render'        => 'block::login',
        'template'      => 'login',
    ),

    // Pi feature block
    'pi'    => array(
        'title'         => _a('Pi feature'),
        'description'   => _a('Introduction to Pi Engine'),
        'render'        => 'block::pi',
        'template'      => 'pi',
    ),

);
