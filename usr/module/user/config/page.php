<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
    ),
);
