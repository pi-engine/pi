<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return array(
    // Admin section
    'admin' => array(
        array(
            'controller'    => 'index',
            'permission'    => 'user',
        ),
        array(
            'controller'    => 'role',
            'permission'    => 'role',
        ),
        array(
            'controller'    => 'profile',
            'permission'    => 'profile',
        ),
        array(
            'controller'    => 'import',
            'permission'    => 'import',
        ),
        array(
            'controller'    => 'avatar',
            'permission'    => 'avatar',
        ),
        array(
            'controller'    => 'form',
            'permission'    => 'form',
        ),
        array(
            'controller'    => 'plugin',
            'permission'    => 'plugin',
        ),
        array(
            'controller'    => 'maintenance',
            'permission'    => 'maintenance',
        ),
        array(
            'controller'    => 'inquiry',
            'permission'    => 'inquiry',
        ),
        array(
            'controller'    => 'condition',
            'permission'    => 'condition',
        ),
    ),
    // Front section
    'front' => array(
        array(
            'title'         => _a('Profile view'),
            'controller'    => 'profile',
            'action'    => 'index',
            'permission'    => 'profile-page',
            'block'         => 1,
        ),
        array(
            'title'         => _a('Activities view'),
            'controller'    => 'home',
            'action'        => 'index',
            'permission'    => 'profile-page',
            'block'         => 1,
        ),
        array(
            'title'         => _a('Dashboard view'),
            'controller'    => 'dashboard',
            'action'        => 'index',
            'permission'    => 'profile-page',
            'block'         => 1,
        ),
    ),
);
